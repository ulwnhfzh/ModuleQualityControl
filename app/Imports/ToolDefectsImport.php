<?php

namespace App\Imports;

use App\Models\ToolDefect;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($filePath);

        $validDivisions = array_map(fn($d) => strtoupper(trim($d)), ToolDefect::DIVISIONS);
        $validDefects   = array_map(fn($f) => strtolower(trim($f)), ToolDefect::COUNT_FIELDS);

        // Dukungan nama bulan Bahasa Inggris & Indonesia (huruf besar semua)
        $monthMap = [
            'JANUARY'   => 'JAN', 'JANUARI'  => 'JAN',
            'FEBRUARY'  => 'FEB', 'FEBRUARI' => 'FEB',
            'MARCH'     => 'MAR', 'MARET'    => 'MAR',
            'APRIL'     => 'APR', 'APRIL '   => 'APR',
            'MAY'       => 'MAY', 'MEI'      => 'MAY',
            'JUNE'      => 'JUN', 'JUNI'     => 'JUN',
            'JULY'      => 'JUL', 'JULI'     => 'JUL',
            'AUGUST'    => 'AUG', 'AGUSTUS'  => 'AUG',
            'SEPTEMBER' => 'SEP', 'SEPTEMBER' => 'SEP',
            'OCTOBER'   => 'OCT', 'OKTOBER'  => 'OCT',
            'NOVEMBER'  => 'NOV', 'NOVEMBER' => 'NOV',
            'DECEMBER'  => 'DEC', 'DESEMBER' => 'DEC',
        ];

        $allRows = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $sheetName = strtoupper(trim($sheet->getTitle()));

            if ($sheetName !== 'MASTER REKAP GENERAL') {
                continue;
            }

            // ============================================================
            // 1. CARI BARIS HEADER BULAN & HEADER DEFECT SECARA DINAMIS
            // ============================================================
            // Kadang header bulan tidak selalu di baris 2.
            // Kita scan baris 1-5 untuk cari baris mana yang mengandung 'JANUARY', 'FEBRUARY', dst.
            $headerMonthRowIndex  = null;
            $headerDefectRowIndex = null;

            for ($r = 1; $r <= 5; $r++) {
                $row = $sheet->rangeToArray('A' . $r . ':ZZ' . $r)[0] ?? [];
                $hasMonthName = false;
                foreach ($row as $cell) {
                    $val = strtoupper(trim((string) $cell));
                    if (isset($monthMap[$val])) {
                        $hasMonthName = true;
                        break;
                    }
                }
                if ($hasMonthName && $headerMonthRowIndex === null) {
                    $headerMonthRowIndex = $r;
                    // Baris defect diasumsikan 1-2 baris di bawah header bulan
                    // Kita coba scan baris berikutnya untuk cari 'AUS', 'ERORR', dst.
                    for ($dr = $r + 1; $dr <= $r + 3; $dr++) {
                        $rowDefect = $sheet->rangeToArray('A' . $dr . ':ZZ' . $dr)[0] ?? [];
                        foreach ($rowDefect as $cellDefect) {
                            $valDefect = strtolower(trim(str_replace([' ', '-'], '_', (string) $cellDefect)));
                            if (in_array($valDefect, $validDefects, true)) {
                                $headerDefectRowIndex = $dr;
                                break 2;
                            }
                        }
                    }
                    break;
                }
            }

            if ($headerMonthRowIndex === null || $headerDefectRowIndex === null) {
                // Kalau tidak ketemu header bulan & defect, skip sheet ini
                continue;
            }

            // ============================================================
            // 2. BACA HEADER BULAN & DEFECT
            // ============================================================
            $monthRow  = $sheet->rangeToArray('A' . $headerMonthRowIndex . ':ZZ' . $headerMonthRowIndex)[0] ?? [];
            $defectRow = $sheet->rangeToArray('A' . $headerDefectRowIndex . ':ZZ' . $headerDefectRowIndex)[0] ?? [];

            // ============================================================
            // 3. FORWARD-FILL MERGED CELL (Inti Solusi)
            // ============================================================
            // Jika header bulan di Excel di-merge, hanya sel pertama yang berisi teks,
            // sel-sel setelahnya berisi NULL. Kita perlu mengisi (forward-fill)
            // nilai $currentMonth sampai ketemu nama bulan baru.
            $currentMonth = null;
            $validColumns = [];
            $lastFoundMonth = null;

            foreach ($monthRow as $idx => $cellValue) {
                $val = strtoupper(trim((string) $cellValue));

                // Jika sel ini berisi nama bulan, update currentMonth
                if (isset($monthMap[$val])) {
                    $currentMonth = $monthMap[$val];
                    $lastFoundMonth = $currentMonth;
                }

                // Karena mungkin sel kosong (merged cell), kita gunakan $lastFoundMonth
                // sebagai fallback jika $currentMonth sudah pernah terisi.
                // Tapi kita hanya mau isi jika sedang dalam range bulan yang valid.
                if ($currentMonth !== null && $lastFoundMonth !== null) {
                    $currentMonth = $lastFoundMonth;
                }

                // Cek defect di baris defect
                $defectName = strtolower(trim(str_replace([' ', '-'], '_', (string) ($defectRow[$idx] ?? ''))));

                if ($currentMonth !== null && in_array($defectName, $validDefects, true)) {
                    $validColumns[$idx + 1] = [
                        'bulan'  => $currentMonth,
                        'defect' => $defectName,
                    ];
                }
            }

            if (empty($validColumns)) {
                continue;
            }

            $highestRow = $sheet->getHighestRow();

            // ============================================================
            // 4. BACA DATA MULAI BARIS SETELAH HEADER DEFECT
            // ============================================================
            $startRow = $headerDefectRowIndex + 1;

            for ($rowIndex = $startRow; $rowIndex <= $highestRow; $rowIndex++) {
                // Cari kolom division. Kolom C (index 3) biasanya, tapi kita deteksi lebih aman.
                // Kita coba baca kolom A, B, C untuk cari nama divisi yang valid.
                $division = null;
                for ($divCol = 1; $divCol <= 5; $divCol++) {
                    $cellDiv = strtoupper(trim((string) $sheet->getCell([$divCol, $rowIndex])->getValue()));
                    if ($cellDiv === '' || str_contains($cellDiv, 'TOTAL')) {
                        continue;
                    }
                    foreach ($validDivisions as $vd) {
                        if ($cellDiv === $vd || str_contains($cellDiv, $vd)) {
                            $division = $vd;
                            break 2;
                        }
                    }
                }

                if (!$division) {
                    continue;
                }

                // Siapkan wadah per bulan
                $monthlyData = [];
                foreach (array_values($monthMap) as $m) {
                    $monthlyData[$m] = array_fill_keys($validDefects, 0);
                }

                // Baca nilai per kolom valid
                foreach ($validColumns as $colIdx => $info) {
                    $val = $sheet->getCell([$colIdx, $rowIndex])->getCalculatedValue();
                    if (is_string($val)) {
                        $val = str_replace(',', '.', $val);
                    }
                    if (is_numeric($val)) {
                        $monthlyData[$info['bulan']][$info['defect']] += (float) $val;
                    }
                }

                // Kumpulkan baris yang ada isinya
                foreach ($monthlyData as $bulan => $defects) {
                    if (array_sum($defects) > 0) {
                        $allRows[] = array_merge([
                            'tahun'    => $this->tahun,
                            'bulan'    => $bulan,
                            'division' => $division,
                        ], $defects);
                    }
                }
            }
        }

        if (empty($allRows)) {
            throw new \Exception(
                "Tidak ada data yang masuk. Pastikan sheet bernama 'MASTER REKAP GENERAL', " .
                "terdapat header bulan (JANUARY/DECEMBER) dan header defect (AUS, ERORR, dst) " .
                "di 5 baris pertama."
            );
        }

        // Bulk upsert
        ToolDefect::upsert(
            $allRows,
            ['tahun', 'bulan', 'division'],
            array_values($validDefects)
        );
    }
}