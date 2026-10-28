<?php

namespace App\Imports;

use App\Models\ToolDefect;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ToolDefectsImport
{
    protected int $tahun;

    public function __construct(int $tahun)
    {
        $this->tahun = $tahun;
    }

    public function import($filePath)
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true); 
        $spreadsheet = $reader->load($filePath);

        $allSheets = $spreadsheet->getAllSheets();
        $validDivisions = ToolDefect::DIVISIONS; 
        $validDefects = ToolDefect::COUNT_FIELDS;

        $adaDataYangMasuk = false;

        foreach ($allSheets as $sheet) {
            $sheetName = strtoupper(trim($sheet->getTitle()));

            if (!str_contains($sheetName, 'MASTER REKAP GENERAL')) {
                continue; 
            }

            $monthRow = $sheet->rangeToArray('A2:ZZ2')[0] ?? []; 
            $defectRow = $sheet->rangeToArray('A3:ZZ3')[0] ?? [];

            $monthMap = [
                'JANUARY' => 'JAN', 'FEBRUARY' => 'FEB', 'MARCH' => 'MAR', 'APRIL' => 'APR',
                'MAY' => 'MAY', 'JUNE' => 'JUN', 'JULY' => 'JUL', 'AUGUST' => 'AUG',
                'SEPTEMBER' => 'SEP', 'OCTOBER' => 'OCT', 'NOVEMBER' => 'NOV', 'DECEMBER' => 'DEC'
            ];

            $currentMonth = null;
            $validColumns = [];

            // =========================================================
            // MAPPING KOLOM BERDASARKAN NAMA HEADER (ANTI GESER)
            // =========================================================
            foreach ($monthRow as $colIndex => $cellValue) {
                $val = strtoupper(trim((string) $cellValue));
                
                if (array_key_exists($val, $monthMap)) {
                    $currentMonth = $monthMap[$val];
                }
                
                $defectName = strtoupper(trim((string) ($defectRow[$colIndex] ?? '')));
                
                // Bersihkan nama defect (HILANG-kan spasi, dll)
                $normalizedDefect = str_replace([' ', '-'], '_', strtolower($defectName));

                // JIKA Defect-nya valid, simpan posisi kolom Excel-nya (ditambah 1 karena Index Array mulai dari 0)
                if ($currentMonth !== null && in_array($normalizedDefect, $validDefects)) {
                    $validColumns[$colIndex + 1] = [
                        'bulan' => $currentMonth,
                        'defect' => $normalizedDefect
                    ];
                }
            }

            if (empty($validColumns)) {
                continue;
            }

            $highestRow = $sheet->getHighestRow();

            for ($rowIndex = 4; $rowIndex <= $highestRow; $rowIndex++) {
                
                // Division ada di Kolom C (Index 3)
                $divisionRaw = mb_strtoupper(trim((string) $sheet->getCell([3, $rowIndex])->getValue()));
                
                if ($divisionRaw === '' || str_contains($divisionRaw, 'TOTAL')) {
                    continue;
                }

                $division = $divisionRaw;
                if (!in_array($division, $validDivisions)) {
                    $found = false;
                    foreach($validDivisions as $validDiv) {
                        if (str_contains($division, $validDiv) || str_contains($validDiv, $division)) {
                            $division = $validDiv;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) continue; 
                }

                $monthlyData = [];
                foreach (array_unique(array_values($monthMap)) as $m) {
                    $monthlyData[$m] = array_fill_keys($validDefects, 0);
                }

                // Looping HANYA berdasarkan kolom yang sudah di-Mapping di atas
                foreach ($validColumns as $colIndex => $info) {
                    $bulan = $info['bulan'];
                    $defect = $info['defect'];
                    
                    $cellValue = $sheet->getCell([$colIndex, $rowIndex])->getValue();
                    
                    if (is_string($cellValue)) {
                        $cellValue = str_replace(',', '.', $cellValue);
                    }
                    
                    if (is_numeric($cellValue)) {
                        $monthlyData[$bulan][$defect] += (float) $cellValue;
                    }
                }

                foreach ($monthlyData as $bulan => $defects) {
                    if (array_sum($defects) > 0) {
                        ToolDefect::updateOrCreate(
                            [
                                'tahun' => $this->tahun,
                                'bulan' => $bulan,
                                'division' => $division,
                            ],
                            $defects
                        );
                        $adaDataYangMasuk = true;
                    }
                }
            }
        }

        if (!$adaDataYangMasuk) {
            throw new \Exception("Tidak ada data yang masuk. Pastikan format Excel: Division di Kolom C, Header Bulan di Baris 2, Header Defect di Baris 3.");
        }
    }
}