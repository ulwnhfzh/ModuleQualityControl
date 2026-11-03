<?php

namespace App\Imports;

use App\Models\ToolDefect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ToolDefectsImport
{
    private const MONTHS = [
        'JAN' => ['JAN', 'JANUARY', 'JANUARI'],
        'FEB' => ['FEB', 'FEBRUARY', 'FEBRUARI'],
        'MAR' => ['MAR', 'MARCH', 'MARET'],
        'APR' => ['APR', 'APRIL'],
        'MAY' => ['MAY', 'MEI'],
        'JUN' => ['JUN', 'JUNE', 'JUNI'],
        'JUL' => ['JUL', 'JULY', 'JULI'],
        'AUG' => ['AUG', 'AUGUST', 'AGUSTUS'],
        'SEP' => ['SEP', 'SEPTEMBER'],
        'OCT' => ['OCT', 'OCTOBER', 'OKTOBER'],
        'NOV' => ['NOV', 'NOVEMBER'],
        'DEC' => ['DEC', 'DECEMBER', 'DESEMBER'],
    ];

    public function __construct(private readonly int $tahun)
    {
    }

    public function import(string $filePath): int
    {
        $spreadsheet = IOFactory::createReaderForFile($filePath)
            ->setReadDataOnly(false)
            ->load($filePath);
        $rowsByKey = [];
        $detailRows = [];

        $sheets = $spreadsheet->getAllSheets();
        $generalSheet = collect($sheets)->first(function (Worksheet $sheet): bool {
            $normalizedName = preg_replace('/[^A-Z0-9]+/', '', strtoupper($sheet->getTitle()));
            return $normalizedName === 'MASTERREKAPGENERAL';
        });

        // Prefer the single general source for totals, and read the explicitly
        // enabled LIST UP NG sheet separately for tool-level chart details.
        $detailSheet = collect($sheets)->first(function (Worksheet $sheet): bool {
            $normalizedName = preg_replace('/[^A-Z0-9]+/', '', strtoupper($sheet->getTitle()));
            return $normalizedName === 'LISTUPNG';
        });

        if ($generalSheet !== null) {
            $sheets = [$generalSheet];
        } else {
            $sheets = array_values(array_filter($sheets, fn (Worksheet $sheet): bool => $sheet !== $detailSheet));
        }

        if ($detailSheet !== null) {
            $detailRows = $this->readDetailSheet($detailSheet);
        }

        foreach ($sheets as $sheet) {
            $sheetRows = $this->readSheet($sheet);
            foreach ($sheetRows as $row) {
                $key = $row['tahun'] . '|' . $row['bulan'] . '|' . $row['division'];
                if (!isset($rowsByKey[$key])) {
                    $rowsByKey[$key] = $row;
                    continue;
                }

                foreach (ToolDefect::COUNT_FIELDS as $field) {
                    $rowsByKey[$key][$field] += $row[$field];
                }
            }
        }

        if ($rowsByKey === []) {
            throw new \RuntimeException(
                'Tidak ada data Data NG yang dikenali. Pastikan sheet memuat header bulan, header jenis kerusakan, dan nama divisi.'
            );
        }

        $rows = array_values($rowsByKey);
        DB::transaction(function () use ($rows, $detailRows): void {
            $availableColumns = Schema::getColumnListing((new ToolDefect())->getTable());
            $errorColumn = in_array('error', $availableColumns, true) ? 'error' : 'erorr';
            foreach ($rows as $row) {
                $values = [];
                foreach (ToolDefect::COUNT_FIELDS as $field) {
                    $column = $field === 'erorr' ? $errorColumn : $field;
                    if (in_array($column, $availableColumns, true)) {
                        $values[$column] = (int) ($row[$field] ?? 0);
                    }
                }
                ToolDefect::updateOrCreate(
                    [
                        'tahun' => $row['tahun'],
                        'bulan' => $row['bulan'],
                        'division' => $row['division'],
                    ],
                    $values
                );
            }

            if (Schema::hasTable('tool_defect_details')) {
                DB::table('tool_defect_details')->where('tahun', $this->tahun)->delete();
                foreach (array_chunk($detailRows, 500) as $chunk) {
                    DB::table('tool_defect_details')->insert($chunk);
                }
            }
        });

        return count($rows);
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    private function readSheet(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $header = $this->findHeaders($sheet, $highestRow, $highestColumn);
        if ($header === null) {
            return [];
        }

        [$monthRow, $defectRow, $sheetMonth] = $header;
        $columns = [];
        $currentMonth = $sheetMonth;
        for ($column = 1; $column <= $highestColumn; $column++) {
            if ($monthRow !== null) {
                $month = $this->normalizeMonth($sheet->getCell([$column, $monthRow])->getFormattedValue());
                if ($month !== null) {
                    $currentMonth = $month;
                }
            }

            $defect = $this->normalizeDefect($sheet->getCell([$column, $defectRow])->getFormattedValue());
            if ($currentMonth !== null && $defect !== null) {
                $columns[$column] = ['month' => $currentMonth, 'field' => $defect];
            }
        }

        if ($columns === []) {
            return [];
        }

        $monthsOnSheet = array_values(array_unique(array_column($columns, 'month')));
        $validDivisions = ToolDefect::DIVISIONS;
        usort($validDivisions, fn (string $a, string $b): int => strlen($b) <=> strlen($a));
        $rows = [];

        for ($rowIndex = $defectRow + 1; $rowIndex <= $highestRow; $rowIndex++) {
            $division = $this->findDivision($sheet, $rowIndex, $highestColumn, $validDivisions);
            if ($division === null) {
                continue;
            }

            $monthlyData = [];
            foreach ($monthsOnSheet as $month) {
                $monthlyData[$month] = array_fill_keys(ToolDefect::COUNT_FIELDS, 0);
            }

            foreach ($columns as $column => $mapping) {
                $value = $sheet->getCell([$column, $rowIndex])->getCalculatedValue();
                if (is_numeric($value) && (float) $value >= 0) {
                    $monthlyData[$mapping['month']][$mapping['field']] += (int) round((float) $value);
                }
            }

            foreach ($monthlyData as $month => $defects) {
                $rows[] = array_merge([
                    'tahun' => $this->tahun,
                    'bulan' => $month,
                    'division' => $division,
                ], $defects);
            }
        }

        return $rows;
    }

    /**
     * @return array{0: int|null, 1: int, 2: string|null}|null
     */
    private function findHeaders(Worksheet $sheet, int $highestRow, int $highestColumn): ?array
    {
        $lastHeaderRow = min(10, $highestRow);
        $defectRow = null;
        for ($row = 1; $row <= $lastHeaderRow; $row++) {
            $recognized = 0;
            for ($column = 1; $column <= $highestColumn; $column++) {
                if ($this->normalizeDefect($sheet->getCell([$column, $row])->getFormattedValue()) !== null) {
                    $recognized++;
                }
            }
            if ($recognized >= 3) {
                $defectRow = $row;
                break;
            }
        }

        if ($defectRow === null) {
            return null;
        }

        $monthRow = null;
        for ($row = 1; $row < $defectRow; $row++) {
            for ($column = 1; $column <= $highestColumn; $column++) {
                if ($this->normalizeMonth($sheet->getCell([$column, $row])->getFormattedValue()) !== null) {
                    $monthRow = $row;
                    break 2;
                }
            }
        }

        $sheetMonth = $monthRow === null
            ? $this->normalizeMonth($sheet->getTitle())
            : null;

        return [$monthRow, $defectRow, $sheetMonth];
    }

    private function findDivision(Worksheet $sheet, int $row, int $highestColumn, array $divisions): ?string
    {
        for ($column = 1; $column <= min(10, $highestColumn); $column++) {
            $value = $this->normalizeText($sheet->getCell([$column, $row])->getFormattedValue());
            if ($value === '') {
                continue;
            }
            foreach ($divisions as $division) {
                if ($value === $this->normalizeText($division)) {
                    return $division;
                }
            }
        }

        return null;
    }

    private function normalizeMonth(mixed $value): ?string
    {
        $normalized = strtoupper(trim((string) $value));
        foreach (self::MONTHS as $month => $aliases) {
            if (in_array($normalized, $aliases, true)) {
                return $month;
            }
        }

        return null;
    }

    private function normalizeDefect(mixed $value): ?string
    {
        $normalized = strtoupper(trim((string) $value));
        $normalized = preg_replace('/[\s-]+/', '_', $normalized);
        if ($normalized === 'ERROR' || $normalized === 'EROR' || $normalized === 'ERORR') {
            return 'erorr';
        }
        if ($normalized === 'LAIN_LAIN') {
            return 'lain_lain';
        }
        if (in_array($normalized, ['MIN_STD', 'OVER_STD'], true)) {
            return strtolower($normalized);
        }

        $field = strtolower($normalized);
        return in_array($field, ToolDefect::COUNT_FIELDS, true) ? $field : null;
    }

    private function normalizeText(mixed $value): string
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim((string) $value)));
    }

    /**
     * @return array<int, array<string, int|string|null>>
     */
    private function readDetailSheet(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $headerRow = null;
        $columns = [];
        $aliases = [
            'tool_name' => ['TOOL NAME', 'NAMA ALAT', 'ALAT UKUR', 'INSTRUMENT NAME'],
            'division' => ['DIVISION', 'DEPARTMENT', 'DEPT', 'DEPT.', 'DIVISI'],
            'defect_type' => ['JENIS KERUSAKAN', 'DEFECT', 'DEFECT TYPE', 'NG CATEGORY', 'CATEGORY', 'CATEGORY NG'],
            'tanggal' => ['TANGGAL', 'DATE', 'NG DATE', 'TGL'],
            'serial_no' => ['SERIAL NO', 'SERIAL NUMBER', 'SERIAL', 'NO SERI', 'NUMBER SERI'],
        ];

        for ($row = 1; $row <= min(15, $highestRow); $row++) {
            $candidate = [];
            for ($column = 1; $column <= $highestColumn; $column++) {
                $label = $this->normalizeText($sheet->getCell([$column, $row])->getFormattedValue());
                foreach ($aliases as $field => $names) {
                    if (in_array($label, $names, true)) {
                        $candidate[$field] = $column;
                    }
                }
            }
            if (isset($candidate['tool_name']) && (isset($candidate['division']) || isset($candidate['defect_type']))) {
                $headerRow = $row;
                $columns = $candidate;
                break;
            }
        }

        if ($headerRow === null) {
            return [];
        }

        $rows = [];
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $name = trim((string) $sheet->getCell([$columns['tool_name'], $row])->getFormattedValue());
            if ($name === '') {
                continue;
            }
            $dateValue = isset($columns['tanggal'])
                ? $sheet->getCell([$columns['tanggal'], $row])->getValue()
                : null;
            if (is_numeric($dateValue) && $dateValue > 20000) {
                $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $dateValue)->format('Y-m-d');
            } else {
                $timestamp = strtotime((string) $dateValue);
                $dateValue = $timestamp ? date('Y-m-d', $timestamp) : null;
            }
            $rows[] = [
                'tahun' => $this->tahun,
                'tool_name' => mb_substr($name, 0, 255),
                'division' => isset($columns['division']) ? mb_substr(trim((string) $sheet->getCell([$columns['division'], $row])->getFormattedValue()), 0, 100) : null,
                'defect_type' => isset($columns['defect_type']) ? mb_substr(trim((string) $sheet->getCell([$columns['defect_type'], $row])->getFormattedValue()), 0, 100) : null,
                'tanggal' => $dateValue,
                'serial_no' => isset($columns['serial_no']) ? mb_substr(trim((string) $sheet->getCell([$columns['serial_no'], $row])->getFormattedValue()), 0, 100) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }
}
