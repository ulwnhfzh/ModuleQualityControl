<?php

namespace App\Imports;

use App\Models\DataAllProduksi;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

class DataAllProduksiImport implements WithMultipleSheets
{
    private int $sheetCount;

    public function sheets(): array
    {
        // Kunci indeks numerik membuat seluruh sheet dibaca tanpa bergantung nama/urutannya.
        $sheets = [];
        for ($index = 0; $index < $this->sheetCount; $index++) {
            $sheets[$index] = new DataAllProduksiSheetImport();
        }
        return $sheets;
    }

    public function __construct(string $filePath)
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $book = $reader->load($filePath);
        $this->sheetCount = $book->getSheetCount();
        $book->disconnectWorksheets();
    }
}

/** Reads each sheet by its actual header labels, including the two-level month header. */
class DataAllProduksiSheetImport implements WithEvents
{
    private const VALID_DIVISIONS = [
        'QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP',
        'PPIC 1', 'PPIC 2', 'LINE SCC',
        'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'ENG', 'NISSHO',
    ];

    private const MONTHS = [
        'JAN' => 'jan', 'JANUARI' => 'jan',
        'FEB' => 'feb', 'FEBRUARI' => 'feb',
        'MAR' => 'mar', 'MARET' => 'mar',
        'APR' => 'apr', 'APRIL' => 'apr',
        'MAY' => 'may', 'MEI' => 'may',
        'JUN' => 'jun', 'JUNI' => 'jun',
        'JUL' => 'jul', 'JULI' => 'jul',
        'AUG' => 'aug', 'AGU' => 'aug', 'AGUSTUS' => 'aug',
        'SEP' => 'sep', 'SEPTEMBER' => 'sep',
        'OCT' => 'oct', 'OKT' => 'oct', 'OKTOBER' => 'oct',
        'NOV' => 'nov', 'NOVEMBER' => 'nov',
        'DEC' => 'dec', 'DES' => 'dec', 'DESEMBER' => 'dec',
    ];

    public function registerEvents(): array
    {
        return [BeforeSheet::class => function (BeforeSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $lastRow = $sheet->getHighestDataRow();
            $lastColumn = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
            $headers = [];
            $monthColumns = [];
            $headerRow = null;
            $monthHeaderRow = null;

            // Locate TOOL NAME/TYPE/SIZE row; do not assume fixed row 2/4.
            for ($row = 1; $row <= min($lastRow, 30); $row++) {
                $values = [];
                for ($col = 1; $col <= $lastColumn; $col++) {
                    $text = $this->normalize($sheet->getCell([$col, $row])->getFormattedValue());
                    if ($text !== '') $values[$text] = $col;
                }
                if (isset($values['TOOL NAME'], $values['TYPE'], $values['SIZE'])) {
                    $headerRow = $row;
                    $headers = $values;
                    break;
                }
            }

            // Month labels may be on a second header row, often under a merged RESULT label.
            if ($headerRow !== null) {
                for ($row = $headerRow; $row <= min($lastRow, $headerRow + 4); $row++) {
                    for ($col = 1; $col <= $lastColumn; $col++) {
                        $label = $this->normalize($sheet->getCell([$col, $row])->getFormattedValue());
                        if (isset(self::MONTHS[$label])) {
                            $monthColumns[self::MONTHS[$label]] = $col;
                            $monthHeaderRow = max($monthHeaderRow ?? 0, $row);
                        }
                    }
                }
            }

            // Non-data sheets (cover, chart, label) are ignored. A sheet with a table header
            // but no monthly header is a malformed input and must not silently corrupt rows.
            if ($headerRow === null) return;
            if (!$monthColumns) {
                throw new RuntimeException('Sheet "' . $sheet->getTitle() . '" memiliki header alat, tetapi header bulan JAN–DEC tidak ditemukan.');
            }

            $sheetDivision = $this->normalize($sheet->getTitle());
            $division = $this->canonicalDivision($sheetDivision);
            if (!in_array($division, self::VALID_DIVISIONS, true)) {
                throw new RuntimeException('Division "' . $sheet->getTitle() . '" tidak dikenal. Import dibatalkan. Gunakan nama sheet division yang terdaftar, misalnya QA 1, LINE SCC, P1, atau ENG.');
            }

            $fieldColumns = [
                'tool_name' => $headers['TOOL NAME'],
                'type' => $headers['TYPE'],
                'size' => $headers['SIZE'],
                'number_seri' => $headers['NUMBER SERI'] ?? $headers['NUMBER SERI.'] ?? null,
                'number_reg' => $headers['NUMBER REG'] ?? $headers['NUMBER REG.'] ?? null,
                'part_name' => $headers['PART NAME'] ?? null,
                'part_number' => $headers['PART NUMBER'] ?? null,
                'note' => $headers['NOTE'] ?? null,
                'division' => $headers['DIVISION'] ?? null,
            ];
            $firstDataRow = max($headerRow, $monthHeaderRow ?? $headerRow) + 1;
            DB::transaction(function () use ($sheet, $lastRow, $fieldColumns, $monthColumns, $firstDataRow, $division): void {
                for ($row = $firstDataRow; $row <= $lastRow; $row++) {
                    $toolName = $this->cellText($sheet, $fieldColumns['tool_name'], $row);
                    if ($toolName === null || $toolName === '') continue;

                    $rowDivision = $this->cellText($sheet, $fieldColumns['division'], $row);
                    if ($rowDivision !== null && $this->canonicalDivision($rowDivision) !== $division) {
                        throw new RuntimeException('Division pada baris ' . $row . ' di sheet "' . $sheet->getTitle() . '" tidak sesuai dengan division sheet. Seluruh import dibatalkan.');
                    }

                    $record = [
                        'division' => $division,
                        'tool_name' => $toolName,
                        'type' => $this->cellText($sheet, $fieldColumns['type'], $row) ?? '-',
                        'size' => $this->cellText($sheet, $fieldColumns['size'], $row) ?? '-',
                        'number_seri' => $this->cellText($sheet, $fieldColumns['number_seri'], $row),
                        'number_reg' => $this->cellText($sheet, $fieldColumns['number_reg'], $row),
                        'part_name' => $this->cellText($sheet, $fieldColumns['part_name'], $row),
                        'part_number' => $this->cellText($sheet, $fieldColumns['part_number'], $row),
                        'note' => $this->cellText($sheet, $fieldColumns['note'], $row),
                    ];
                    foreach (array_unique(array_values(self::MONTHS)) as $month) {
                        $column = $monthColumns[$month] ?? null;
                        $record[$month] = $this->cellText($sheet, $column, $row);
                    }

                    // Re-import updates the same tool record instead of duplicating it.
                    DataAllProduksi::updateOrCreate([
                        'division' => $division,
                        'tool_name' => $record['tool_name'],
                        'size' => $record['size'],
                    ], $record);

                }
            });
        }];
    }

    private function cellText($sheet, ?int $column, int $row): ?string
    {
        if ($column === null) return null;
        $value = $sheet->getCell([$column, $row])->getFormattedValue();
        if ($value === null) return null;
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function normalize($value): string
    {
        $value = mb_strtoupper(trim((string) $value));
        return preg_replace('/\s+/', ' ', str_replace(["\r", "\n"], ' ', $value));
    }

    private function canonicalDivision(string $value): string
    {
        $value = $this->normalize($value);
        return match ($value) {
            'LSCC', 'L.SCC' => 'LINE SCC',
            'QA INCOMING' => 'QA INC',
            'QA INSPECTION' => 'QA INSP',
            'ENGINEERING' => 'ENG',
            'PRODUKSI 1' => 'P1',
            'PRODUKSI 2' => 'P2',
            'PRODUKSI 3' => 'P3',
            'PRODUKSI 5' => 'P5',
            'PRODUKSI 6' => 'P6',
            'PRODUKSI 7' => 'P7',
            'PRODUKSI 8' => 'P8',
            default => $value,
        };
    }
}
