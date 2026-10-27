<?php

namespace App\Imports;

use App\Models\DataAllProduksi;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DataAllProduksiImport implements WithMultipleSheets
{
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function sheets(): array
    {
        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($this->filePath);
        $sheetCount = $spreadsheet->getSheetCount();
        $spreadsheet->disconnectWorksheets();

        $sheets = [];
        for ($i = 0; $i < $sheetCount; $i++) {
            $sheets[$i] = new SingleSheetImport();
        }

        return $sheets;
    }
}

class SingleSheetImport implements ToModel, WithStartRow, WithEvents
{
    private $sheetName;

    public function startRow(): int
    {
        return 4;
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function(BeforeSheet $event) {
                $rawName = $event->sheet->getDelegate()->getTitle();
                $this->sheetName = strtoupper(trim($rawName)); 
            },
        ];
    }

    public function model(array $row)
    {
        if (empty($row[1])) {
            return null;
        }

        // Langsung simpan sebagai string/teks (TIDAK DIPAKSA ANGKA)
        return new DataAllProduksi([
            'division'    => $this->sheetName ?? 'UNKNOWN',
            'tool_name'   => $row[1] ?? '-',
            'type'        => $row[2] ?? '-',
            'size'        => $row[3] ?? '-',
            'number_seri' => $row[4] ?? null,
            'number_reg'  => $row[5] ?? null,
            'part_name'   => $row[6] ?? null,
            'part_number' => $row[7] ?? null,
            'jan'         => isset($row[8]) ? (string) $row[8] : null,
            'feb'         => isset($row[9]) ? (string) $row[9] : null,
            'mar'         => isset($row[10]) ? (string) $row[10] : null,
            'apr'         => isset($row[11]) ? (string) $row[11] : null,
            'may'         => isset($row[12]) ? (string) $row[12] : null,
            'jun'         => isset($row[13]) ? (string) $row[13] : null,
            'jul'         => isset($row[14]) ? (string) $row[14] : null,
            'aug'         => isset($row[15]) ? (string) $row[15] : null,
            'sep'         => isset($row[16]) ? (string) $row[16] : null,
            'oct'         => isset($row[17]) ? (string) $row[17] : null,
            'nov'         => isset($row[18]) ? (string) $row[18] : null,
            'dec'         => isset($row[19]) ? (string) $row[19] : null,
            'note'        => $row[20] ?? null,
        ]);
    }
}