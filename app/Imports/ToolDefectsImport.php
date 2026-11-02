<?php

namespace App\Imports;

use App\Models\ToolDefect;
use Illuminate\Support\Facades\DB;
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

        foreach ($spreadsheet->getAllSheets() as $sheet) {
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
        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                ToolDefect::updateOrCreate(
                    [
                        'tahun' => $row['tahun'],
                        'bulan' => $row['bulan'],
                        'division' => $row['division'],
                    ],
                    array_intersect_key($row, array_flip(ToolDefect::COUNT_FIELDS))
                );
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
}
