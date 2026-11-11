<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InternalVerificationSchedule extends Model
{
    protected $table = 'verifikasi_internal_schedules';

    protected array $productionSyncPrevious = [];

    protected static function booted(): void
    {
        static::updating(function (self $schedule): void {
            $schedule->productionSyncPrevious = $schedule->getOriginal();
        });

        static::saved(function (self $schedule): void {
            if ($schedule->productionSyncPrevious !== []) {
                $schedule->syncProductionResult($schedule->productionSyncPrevious);
            }
            $schedule->syncProductionResult($schedule->getAttributes());
            $schedule->productionSyncPrevious = [];
        });

        static::deleted(function (self $schedule): void {
            $schedule->syncProductionResult($schedule->getAttributes());
        });
    }

    public static function syncForProductionRow(ProductionData $productionRow): void
    {
        $year = (int) now()->year;
        $toolName = strtoupper(trim((string) $productionRow->tool_name));
        if ($toolName === '') return;

        $schedules = static::query()
            ->where('tahun', $year)
            ->where('company', 'HAMATETSU')
            ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
            ->get()
            ->filter(fn (self $schedule): bool =>
                $schedule->canonicalDivision($schedule->shop) === $schedule->canonicalDivision($productionRow->division)
                && $schedule->matchesProductionIdentity($schedule->getAttributes(), $productionRow)
            );

        foreach ($schedules as $schedule) {
            $schedule->syncProductionResult($schedule->getAttributes());
        }
    }

    public static function syncAllProductionRows(): void
    {
        $year = (int) now()->year;
        $helper = new static();
        $groups = static::query()
            ->where('tahun', $year)
            ->where('company', 'HAMATETSU')
            ->get()
            ->groupBy(fn (self $schedule): string =>
                ($helper->canonicalDivision($schedule->shop) ?? '') . '|' . $helper->normalizeKey($schedule->tool_name)
            );

        ProductionData::query()->orderBy('id')->chunkById(300, function ($productionRows) use ($groups, $helper, $year): void {
            foreach ($productionRows as $productionRow) {
                $key = ($helper->canonicalDivision($productionRow->division) ?? '') . '|' . $helper->normalizeKey($productionRow->tool_name);
                $relatedSchedules = $groups->get($key, collect())
                    ->filter(fn (self $schedule): bool => $helper->matchesProductionIdentity($schedule->getAttributes(), $productionRow));
                if ($relatedSchedules->isEmpty()) continue;

                $touchedMonths = [];
                $statuses = [];
                foreach ($relatedSchedules as $schedule) {
                    foreach ([
                        ['date' => 'period1_verification_date', 'jgm' => 'period1_jgm'],
                        ['date' => 'period2_verification_date', 'jgm' => 'period2_jgm'],
                    ] as $period) {
                        $dateValue = $schedule->getAttribute($period['date']);
                        if (!$dateValue) continue;
                        $date = Carbon::parse($dateValue);
                        if ($date->year !== $year) continue;
                        $month = strtolower($date->format('M'));
                        $touchedMonths[$month] = true;
                        $status = strtoupper(trim((string) $schedule->getAttribute($period['jgm'])));
                        if (!in_array($status, ['OK', 'NG'], true)) continue;
                        if (!isset($statuses[$month]) || $date->greaterThanOrEqualTo($statuses[$month]['date'])) {
                            $statuses[$month] = ['status' => $status, 'date' => $date];
                        }
                    }
                }

                foreach (array_keys($touchedMonths) as $month) {
                    $productionRow->setAttribute($month, $statuses[$month]['status'] ?? null);
                }
                if ($touchedMonths !== []) $productionRow->saveQuietly();
            }
        });
    }

    private function syncProductionResult(array $identity): void
    {
        $year = (int) ($identity['tahun'] ?? 0);
        // Data All Produksi has no year field; only synchronize the current year's schedule.
        if ($year !== (int) now()->year || strtoupper((string) ($identity['company'] ?? 'HAMATETSU')) !== 'HAMATETSU') {
            return;
        }

        $touchedMonths = [];
        foreach (['period1_verification_date', 'period2_verification_date'] as $field) {
            if (empty($identity[$field])) continue;
            $date = Carbon::parse($identity[$field]);
            if ($date->year === $year) $touchedMonths[] = strtolower($date->format('M'));
        }
        $touchedMonths = array_unique($touchedMonths);
        if ($touchedMonths === []) return;

        $division = $this->canonicalDivision($identity['shop'] ?? null);
        $toolName = $this->normalizeKey($identity['tool_name'] ?? null);
        if ($division === null || $toolName === '') return;

        $productionRows = ProductionData::query()
            ->whereIn('division', $division === 'LINE SCC' ? ['LINE SCC', 'LSCC', 'L.SCC'] : [$division])
            ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
            ->get()
            ->filter(fn (ProductionData $row): bool => $this->matchesProductionIdentity($identity, $row));

        // Do not guess if multiple equipment rows share a name and cannot be distinguished.
        if ($productionRows->count() !== 1) return;
        $productionRow = $productionRows->first();

        $schedules = self::query()
            ->where('tahun', $year)
            ->where('company', 'HAMATETSU')
            ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$this->normalizeKey($productionRow->tool_name)])
            ->get()
            ->filter(fn (self $row): bool =>
                $this->canonicalDivision($row->shop) === $this->canonicalDivision($productionRow->division)
                && $this->matchesProductionIdentity($row->getAttributes(), $productionRow)
            );

        $statuses = [];
        foreach ($schedules as $row) {
            foreach ([
                ['date' => 'period1_verification_date', 'jgm' => 'period1_jgm'],
                ['date' => 'period2_verification_date', 'jgm' => 'period2_jgm'],
            ] as $period) {
                $dateValue = $row->getAttribute($period['date']);
                $status = strtoupper(trim((string) $row->getAttribute($period['jgm'])));
                if (!$dateValue || !in_array($status, ['OK', 'NG'], true)) continue;
                $date = Carbon::parse($dateValue);
                if ($date->year !== $year) continue;
                $month = strtolower($date->format('M'));
                if (!isset($statuses[$month]) || $date->greaterThanOrEqualTo($statuses[$month]['date'])) {
                    $statuses[$month] = ['status' => $status, 'date' => $date];
                }
            }
        }

        foreach ($touchedMonths as $month) {
            $productionRow->setAttribute($month, $statuses[$month]['status'] ?? null);
        }
        $productionRow->saveQuietly();
    }

    private function matchesProductionIdentity(array $schedule, ProductionData $row): bool
    {
        $registration = $this->normalizeKey($schedule['registration_number'] ?? null);
        $serial = $this->normalizeKey($schedule['serial_number'] ?? null);
        $specification = $this->normalizeKey($schedule['specification'] ?? null);
        if ($registration !== '' && $registration !== $this->normalizeKey($row->number_reg)) return false;
        if ($serial !== '' && $serial !== $this->normalizeKey($row->number_seri)) return false;
        if ($registration === '' && $serial === '' && $specification !== '') {
            return $specification === $this->normalizeKey($row->size);
        }
        if ($registration === '' && $serial === '' && $specification === '') {
            $type = $this->normalizeKey($schedule['tool_type'] ?? null);
            return $type === '' || $type === $this->normalizeKey($row->type);
        }
        return true;
    }

    private function canonicalDivision(mixed $value): ?string
    {
        $value = $this->normalizeKey($value);
        if ($value === '') return null;
        return in_array($value, ['LSCC', 'L.SCC', 'LINE SCC'], true) ? 'LINE SCC' : $value;
    }

    private function normalizeKey(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        return in_array($value, ['-', 'N/A', 'NA'], true) ? '' : $value;
    }

    protected $fillable = [
        'tool_id', 'tahun', 'company', 'tool_name', 'tool_type', 'specification', 'serial_number', 'registration_number', 'shop',
        'period1_verification_date', 'period1_next_verification_date', 'period1_jgm',
        'period2_verification_date', 'period2_next_verification_date', 'period2_jgm',
    ];

    protected $casts = [
        'period1_verification_date' => 'date',
        'period1_next_verification_date' => 'date',
        'period2_verification_date' => 'date',
        'period2_next_verification_date' => 'date',
    ];

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }
}
