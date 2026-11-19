<?php

namespace App\Services;

use App\Models\ListUpNg;
use App\Models\ToolDefect;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ListUpNgRecapSynchronizer
{
    private static int $suspensionDepth = 0;

    private const CATEGORY_TERMS = [
        'aus' => ['AUS'],
        'erorr' => ['ERORR', 'ERROR'],
        'gompal' => ['GOMPAL'],
        'hilang' => ['HILANG'],
        'karat' => ['KARAT'],
        'macet' => ['MACET'],
        'min_std' => ['MIN STD'],
        'over_std' => ['OVER STD'],
        'patah' => ['PATAH'],
        'rusak' => ['RUSAK'],
        'seret' => ['SERET'],
        'lain_lain' => ['LAIN LAIN'],
    ];

    public static function withoutAutomaticSync(callable $callback): mixed
    {
        self::$suspensionDepth++;

        try {
            return $callback();
        } finally {
            self::$suspensionDepth--;
        }
    }

    public static function isAutomaticSyncSuspended(): bool
    {
        return self::$suspensionDepth > 0;
    }

    public function syncYear(int $year): void
    {
        $rows = [];
        foreach (ToolDefect::DIVISIONS as $division) {
            foreach (ToolDefect::MONTHS as $month) {
                $rows[$month . '|' . $division] = [
                    'tahun' => $year,
                    'bulan' => $month,
                    'division' => $division,
                    ...array_fill_keys(ToolDefect::COUNT_FIELDS, 0),
                ];
            }
        }

        $details = [];
        foreach (ListUpNg::query()->where('tahun', $year)->get() as $ng) {
            $division = $this->canonicalDivision($ng);
            if ($division === null) {
                throw new RuntimeException("Division '{$ng->dept}' tidak dapat dipetakan ke Master Rekap Data NG.");
            }

            $month = strtoupper(Carbon::parse($ng->ng_date)->format('M'));
            $key = $month . '|' . $division;
            if (! isset($rows[$key])) {
                throw new RuntimeException("Periode {$month} / {$division} tidak tersedia di Master Rekap Data NG.");
            }

            $categories = $this->categoriesFromNote($ng->note);
            foreach ($categories === [] ? ['lain_lain'] : $categories as $category) {
                $rows[$key][$category]++;
            }

            $details[] = [
                'tahun' => $year,
                'tool_name' => mb_substr($ng->tool_name, 0, 255),
                'division' => mb_substr($division, 0, 100),
                'defect_type' => mb_substr($ng->category_ng, 0, 100),
                'tanggal' => $ng->ng_date?->format('Y-m-d'),
                'serial_no' => $ng->number_seri === null ? null : mb_substr($ng->number_seri, 0, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $columns = Schema::getColumnListing((new ToolDefect())->getTable());
        $errorColumn = in_array('error', $columns, true) ? 'error' : 'erorr';
        foreach ($rows as &$row) {
            if ($errorColumn !== 'erorr') {
                $row[$errorColumn] = $row['erorr'];
                unset($row['erorr']);
            }
            $row['created_at'] = now();
            $row['updated_at'] = now();
        }
        unset($row);

        DB::transaction(function () use ($year, $rows, $details): void {
            DB::table('tool_defects')->where('tahun', $year)->delete();
            foreach (array_chunk(array_values($rows), 500) as $chunk) {
                DB::table('tool_defects')->insert($chunk);
            }

            if (Schema::hasTable('tool_defect_details')) {
                DB::table('tool_defect_details')->where('tahun', $year)->delete();
                foreach (array_chunk($details, 500) as $chunk) {
                    if ($chunk !== []) {
                        DB::table('tool_defect_details')->insert($chunk);
                    }
                }
            }
        });
    }

    /**
     * Apply only the changes between two sets of List Up NG records. This keeps
     * an imported Master Rekap as the starting total instead of rebuilding it
     * from notes that may not represent every imported count.
     *
     * @param array<int, array<string, mixed>> $oldRecords
     * @param array<int, array<string, mixed>> $newRecords
     */
    public function syncChanges(array $oldRecords, array $newRecords): void
    {
        if (self::isAutomaticSyncSuspended()) {
            return;
        }

        $deltas = [];
        $years = [];
        $this->accumulateChanges($oldRecords, -1, $deltas, $years);
        $this->accumulateChanges($newRecords, 1, $deltas, $years);

        $rebuiltYears = [];
        foreach (array_keys($years) as $year) {
            if (! ToolDefect::query()->where('tahun', $year)->exists()) {
                $this->syncYear($year);
                $rebuiltYears[$year] = true;
            }
        }

        $groupedDeltas = [];
        foreach ($deltas as $key => $delta) {
            if ($delta === 0) {
                continue;
            }

            [$year, $month, $division, $field] = explode('|', $key, 4);
            if (isset($rebuiltYears[$year])) {
                continue;
            }

            $groupedDeltas[$year][$month][$division][$field] = $delta;
        }

        if ($groupedDeltas !== []) {
            $availableColumns = Schema::getColumnListing((new ToolDefect())->getTable());
            $errorColumn = in_array('error', $availableColumns, true) ? 'error' : 'erorr';

            DB::transaction(function () use ($groupedDeltas, $availableColumns, $errorColumn): void {
                foreach ($groupedDeltas as $year => $months) {
                    foreach ($months as $month => $divisions) {
                        foreach ($divisions as $division => $fieldDeltas) {
                            $key = [
                                'tahun' => (int) $year,
                                'bulan' => $month,
                                'division' => $division,
                            ];
                            $query = DB::table('tool_defects')->where($key);
                            $row = $query->first();

                            if ($row === null) {
                                $values = $key + [
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                                foreach (ToolDefect::COUNT_FIELDS as $field) {
                                    $column = $field === 'erorr' ? $errorColumn : $field;
                                    if (in_array($column, $availableColumns, true)) {
                                        $values[$column] = 0;
                                    }
                                }
                                DB::table('tool_defects')->insert($values);
                                $row = DB::table('tool_defects')->where($key)->orderByDesc('id')->first();
                            }

                            $updates = [];
                            foreach ($fieldDeltas as $field => $delta) {
                                $column = $field === 'erorr' ? $errorColumn : $field;
                                if (! in_array($column, $availableColumns, true)) {
                                    continue;
                                }

                                $current = (int) ($row->{$column} ?? 0);
                                $next = $current + $delta;
                                if ($next < 0) {
                                    Log::warning('Perubahan List Up NG melebihi nilai kategori pada Master Rekap; nilai dijaga agar tidak negatif.', [
                                        ...$key,
                                        'category' => $field,
                                        'current' => $current,
                                        'delta' => $delta,
                                    ]);
                                    $next = 0;
                                }
                                $updates[$column] = $next;
                            }

                            if ($updates !== []) {
                                $updates['updated_at'] = now();
                                DB::table('tool_defects')->where('id', $row->id)->update($updates);
                            }
                        }
                    }
                }
            });
        }

        foreach (array_keys($years) as $year) {
            if (! isset($rebuiltYears[$year])) {
                $this->syncDetailsYear($year);
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @param array<string, int> $deltas
     * @param array<int, bool> $years
     */
    private function accumulateChanges(array $records, int $direction, array &$deltas, array &$years): void
    {
        foreach ($records as $attributes) {
            $ng = new ListUpNg();
            $ng->setRawAttributes($attributes, true);
            $date = Carbon::parse($ng->ng_date);
            $year = (int) ($ng->tahun ?: $date->year);
            $division = $this->canonicalDivision($ng);
            if ($division === null) {
                throw new RuntimeException("Division '{$ng->dept}' tidak dapat dipetakan ke Master Rekap Data NG.");
            }

            $month = strtoupper($date->format('M'));
            if (! in_array($month, ToolDefect::MONTHS, true)) {
                throw new RuntimeException("Periode {$month} / {$division} tidak tersedia di Master Rekap Data NG.");
            }

            $years[$year] = true;
            $categories = $this->categoriesFromNote($ng->note);
            foreach ($categories === [] ? ['lain_lain'] : $categories as $category) {
                $key = implode('|', [$year, $month, $division, $category]);
                $deltas[$key] = ($deltas[$key] ?? 0) + $direction;
            }
        }
    }

    private function syncDetailsYear(int $year): void
    {
        if (! Schema::hasTable('tool_defect_details')) {
            return;
        }

        $details = [];
        foreach (ListUpNg::query()->where('tahun', $year)->get() as $ng) {
            $division = $this->canonicalDivision($ng);
            if ($division === null) {
                throw new RuntimeException("Division '{$ng->dept}' tidak dapat dipetakan ke Master Rekap Data NG.");
            }

            $details[] = [
                'tahun' => $year,
                'tool_name' => mb_substr($ng->tool_name, 0, 255),
                'division' => mb_substr($division, 0, 100),
                'defect_type' => mb_substr($ng->category_ng, 0, 100),
                'tanggal' => $ng->ng_date?->format('Y-m-d'),
                'serial_no' => $ng->number_seri === null ? null : mb_substr($ng->number_seri, 0, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($year, $details): void {
            DB::table('tool_defect_details')->where('tahun', $year)->delete();
            foreach (array_chunk($details, 500) as $chunk) {
                if ($chunk !== []) {
                    DB::table('tool_defect_details')->insert($chunk);
                }
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function categoriesFromNote(?string $note): array
    {
        $normalized = ' ' . $this->normalizeNote($note) . ' ';
        $categories = [];

        foreach (self::CATEGORY_TERMS as $field => $terms) {
            foreach ($terms as $term) {
                $normalizedTerm = ' ' . $this->normalizeNote($term) . ' ';
                if (str_contains($normalized, $normalizedTerm)) {
                    $categories[] = $field;
                    break;
                }
            }
        }

        return $categories;
    }

    private function normalizeNote(?string $note): string
    {
        $note = mb_strtoupper(trim((string) $note));
        $note = preg_replace('/[^[:alnum:]]+/u', ' ', $note) ?? '';

        return trim(preg_replace('/\s+/', ' ', $note) ?? '');
    }

    private function canonicalDivision(ListUpNg $ng): ?string
    {
        $division = $this->canonicalDivisionName($ng->dept);
        if ($division !== null) {
            return $division;
        }

        $matches = app(EquipmentIdentityMatcher::class)->productionMatches($ng);
        if ($matches->count() !== 1) {
            return null;
        }

        return $this->canonicalDivisionName($matches->first()->division);
    }

    private function canonicalDivisionName(string $division): ?string
    {
        $division = mb_strtoupper(trim($division));
        $division = preg_replace('/\s+/', ' ', $division) ?? '';

        $canonical = match ($division) {
            'QA1' => 'QA 1',
            'QA2' => 'QA 2',
            'LSCC', 'LINE SCC' => 'L.SCC',
            'QA INC' => 'QA INCOMING',
            'QA INSP' => 'QA INSPECTION',
            'ENG' => 'ENGINEERING',
            'P1' => 'PRODUKSI 1',
            'P2' => 'PRODUKSI 2',
            'P3' => 'PRODUKSI 3',
            'P5' => 'PRODUKSI 5',
            'P6' => 'PRODUKSI 6',
            'P7' => 'PRODUKSI 7',
            'P8' => 'PRODUKSI 8',
            default => $division,
        };

        return in_array($canonical, ToolDefect::DIVISIONS, true) ? $canonical : null;
    }
}