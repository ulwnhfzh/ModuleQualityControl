<?php

namespace App\Imports;

use App\Models\ProductionData;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RuntimeException;

class ProductionDataImport implements WithMultipleSheets
{
    /** @var array<int, true> */
    private array $sheetIndexes = [];
    private int $importedCount = 0;
    private ?string $category;
    private int $year;

    public function sheets(): array
    {
        $sheets = [];
        foreach (array_keys($this->sheetIndexes) as $index) {
            $sheets[$index] = new ProductionDataSheetImport($this);
        }
        return $sheets;
    }

    public function __construct(string $filePath, ?string $category = null, ?int $year = null)
    {
        $category = trim((string) $category);
        $categoryYear = null;
        if (preg_match('/^DATA ALAT UKUR ALL PRODUKSI\s+TAHUN\s+(20\d{2})$/i', $category, $matches) === 1) {
            $categoryYear = (int) $matches[1];
            $category = 'DATA ALAT UKUR ALL PRODUKSI';
        }
        $this->category = $category !== '' ? $category : null;
        $this->year = $year ?? $categoryYear ?? (int) date('Y');

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $book = $reader->load($filePath);
        try {
            foreach ($book->getAllSheets() as $index => $sheet) {
                if (ProductionDataSheetImport::supportsDivisionTitle($sheet->getTitle())) {
                    $this->sheetIndexes[$index] = true;
                }
            }
        } finally {
            $book->disconnectWorksheets();
        }
    }

    public function getSelectedSheetCount(): int
    {
        return count($this->sheetIndexes);
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function saveImportedRecord(array $record): void
    {
        $identity = [
            'division' => $record['division'],
            'type' => $record['type'],
            'tool_name' => $record['tool_name'],
            'size' => $record['size'],
            'number_seri' => $record['number_seri'],
            'number_reg' => $record['number_reg'],
            'category' => $record['category'],
            'year' => $record['year'],
        ];

        $existing = ProductionData::query()->where($identity)->first();
        if ($existing === null && $this->category !== null) {
            $legacyIdentity = $identity;
            $legacyIdentity['category'] = null;
            $existing = ProductionData::query()->where($legacyIdentity)->first();
        }

        if ($existing !== null) {
            $existing->fill($record)->save();
        } else {
            ProductionData::create($record);
        }

        $this->importedCount++;
    }
}

/** Reads each sheet by its actual header labels, including the two-level month header. */
class ProductionDataSheetImport implements WithEvents
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

    public function __construct(private readonly ProductionDataImport $import)
    {
    }

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

            // Locate the equipment header row without requiring optional TYPE columns.
            for ($row = 1; $row <= min($lastRow, 30); $row++) {
                $values = [];
                for ($col = 1; $col <= $lastColumn; $col++) {
                    $text = $this->normalize($sheet->getCell([$col, $row])->getFormattedValue());
                    if ($text !== '') $values[$text] = $col;
                }
                if (isset($values['TOOL NAME'], $values['SIZE'])) {
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

            $sheetDivision = $this->normalize($sheet->getTitle());
            $division = self::canonicalDivision($sheetDivision);
            if (!in_array($division, self::VALID_DIVISIONS, true)) return;

            if ($headerRow === null) {
                if (strtoupper(trim((string) $this->import->getCategory())) === 'MASTER KALIBRATOR') {
                    $this->importHeaderlessMasterRows($sheet, $division, $lastRow);
                }
                return;
            }

            if (!$monthColumns) {
                if (strtoupper(trim((string) $this->import->getCategory())) === 'MASTER KALIBRATOR') {
                    $this->importHeaderlessMasterRows($sheet, $division, $lastRow);
                    return;
                }

                throw new RuntimeException('Sheet "' . $sheet->getTitle() . '" memiliki header alat, tetapi header bulan JAN–DEC tidak ditemukan.');
            }

            $fieldColumns = [
                'tool_name' => $headers['TOOL NAME'],
                'type' => $headers['TYPE'] ?? null,
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
                        'category' => $this->import->getCategory(),
                        'year' => $this->import->getYear(),
                        'is_year_template' => false,
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

                    $this->import->saveImportedRecord($record);
                }
            });
        }];
    }

    private function importHeaderlessMasterRows($sheet, string $division, int $lastRow): void
    {
        DB::transaction(function () use ($sheet, $division, $lastRow): void {
            for ($row = 1; $row <= $lastRow; $row++) {
                $number = $this->cellText($sheet, 1, $row);
                $toolName = $this->cellText($sheet, 2, $row);
                $type = $this->cellText($sheet, 3, $row);
                $size = $this->cellText($sheet, 4, $row);
                if ($number === null || ! is_numeric($number) || $toolName === null || $size === null) {
                    continue;
                }

                $record = [
                    'division' => $division,
                    'category' => $this->import->getCategory(),
                    'year' => $this->import->getYear(),
                    'is_year_template' => false,
                    'tool_name' => $toolName,
                    'type' => $type ?? '-',
                    'size' => $size,
                    'number_seri' => $this->cellText($sheet, 5, $row),
                    'number_reg' => $this->cellText($sheet, 6, $row),
                    'part_name' => null,
                    'part_number' => null,
                    'note' => null,
                ];
                foreach (array_unique(array_values(self::MONTHS)) as $month) {
                    $record[$month] = null;
                }

                $this->import->saveImportedRecord($record);
            }
        });
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

    private static function canonicalDivision(string $value): string
    {
        $value = mb_strtoupper(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        $value = rtrim($value, '. ');
        return match ($value) {
            'QA1' => 'QA 1',
            'QA2' => 'QA 2',
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

    public static function supportsDivisionTitle(string $title): bool
    {
        return in_array(self::canonicalDivision($title), self::VALID_DIVISIONS, true);
    }
}
