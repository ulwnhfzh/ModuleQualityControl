<?php

namespace App\Exports;

use App\Models\ToolDefect;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ToolDefectsExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function array(): array
    {
        $tahun = $this->tahun;
        $dataTahunan = ToolDefect::where('tahun', $tahun)->get();

        $bulanList = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
        $jenisKerusakan = ['aus', 'erorr', 'gompal', 'hilang', 'karat', 'macet', 'min_std', 'over_std', 'patah', 'rusak', 'seret', 'lain_lain'];
        
        $urutanDivisi = [
            'ENGINEERING', 'L.SCC', 'PRODUKSI 1', 'PRODUKSI 2', 'PRODUKSI 3', 
            'PRODUKSI 5', 'PRODUKSI 6', 'PRODUKSI 7', 'PRODUKSI 8', 
            'PPIC 1', 'PPIC 2', 'QA INCOMING', 'QA INSPECTION', 'QA 1', 
            'QA 1.2', 'QA 2', 'NISSHO'
        ];

        // Susun Data Matrix
        $matrixData = [];
        foreach ($urutanDivisi as $div) {
            foreach ($bulanList as $bln) {
                $matrixData[$div][$bln] = array_fill_keys($jenisKerusakan, 0);
                $matrixData[$div][$bln]['total'] = 0; 
            }
        }

        foreach ($dataTahunan as $row) {
            if (isset($matrixData[$row->division][$row->bulan])) {
                foreach ($jenisKerusakan as $jenis) {
                    $matrixData[$row->division][$row->bulan][$jenis] += $row->$jenis;
                    $matrixData[$row->division][$row->bulan]['total'] += $row->$jenis;
                }
            }
        }

        // ==========================================
        // SUSUN BARIS EXCEL
        // ==========================================
        $rows = [];

        // Baris 1: Judul Utama
        $rows[] = ['DATA RECORD TOOL ALL DIVISION ' . $tahun]; 

        // Baris 2: Header (NO, DIVISION, Bulan, GRAND TOTAL)
        // Setiap bulan mengambil 13 kolom (12 jenis + 1 total)
        $headerBulan = ['NO', 'DIVISION'];
        foreach ($bulanList as $bln) {
            $headerBulan[] = $bln;
            for($i=0; $i<12; $i++) { $headerBulan[] = ''; } // 13 kolom per bulan (1 nama + 12 kosong)
        }
        $headerBulan[] = 'GRAND TOTAL';
        $rows[] = $headerBulan;

        // Baris 3: Header Jenis Kerusakan
        $headerJenis = ['', '']; // Kosongkan untuk NO dan DIVISION (di-merge vertikal)
        $jenisText = [
            'AUS', 'ERORR', 'GOMPAL', 'HILANG', 'KARAT', 'MACET', 
            "MIN\nSTD", "OVER\nSTD", 'PATAH', 'RUSAK', 'SERET', "LAIN-\nLAIN", 
            'TOTAL' // Kolom ke-13: TOTAL masuk ke dalam blok bulan
        ];
        foreach ($bulanList as $bln) {
            foreach ($jenisText as $jenis) {
                $headerJenis[] = $jenis;
            }
        }
        $headerJenis[] = ''; // Kosong untuk GRAND TOTAL (di-merge vertikal)
        $rows[] = $headerJenis;

        // Baris 4 dst: Data Divisi
        $no = 1;
        $footerData = ['', 'TOTAL']; 
        $footerBulanan = array_fill_keys($bulanList, array_fill_keys($jenisKerusakan, 0));
        $footerTotalBulanan = array_fill_keys($bulanList, 0);
        $footerGrandTotal = 0;

        foreach ($urutanDivisi as $divisi) {
            $rowData = [$no++, $divisi];
            $grandTotalDivisi = 0;
            
            foreach ($bulanList as $bulan) {
                $totalBulananDivisi = 0;
                foreach ($jenisKerusakan as $jenis) {
                    $val = $matrixData[$divisi][$bulan][$jenis];
                    $rowData[] = $val ?: ''; 
                    
                    $footerBulanan[$bulan][$jenis] += $val;
                    $totalBulananDivisi += $val;
                }
                
                // Total per Bulan dimasukkan ke dalam blok bulan
                $rowData[] = $totalBulananDivisi ?: '';
                $footerTotalBulanan[$bulan] += $totalBulananDivisi;
                $grandTotalDivisi += $totalBulananDivisi;
            }
            
            $rowData[] = $grandTotalDivisi ?: '';
            $footerGrandTotal += $grandTotalDivisi;
            $rows[] = $rowData;
        }

        // Baris Footer (TOTAL Bawah)
        foreach ($bulanList as $bulan) {
            foreach ($jenisKerusakan as $jenis) {
                $footerData[] = $footerBulanan[$bulan][$jenis] ?: '';
            }
            $footerData[] = $footerTotalBulanan[$bulan] ?: ''; // Total di footer
        }
        $footerData[] = $footerGrandTotal ?: '';
        $rows[] = $footerData;

        return $rows;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 6,   
            'B' => 24,  
        ];
        
        // Kolom jenis kerusakan (13 kolom per bulan: 12 jenis + 1 TOTAL)
        $colIndex = 3;
        for ($i = 1; $i <= 12; $i++) {
            for ($j = 1; $j <= 12; $j++) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                // Kolom ke-13 (TOTAL) dibuat sedikit lebih lebar
                $widths[$colLetter] = ($j == 12) ? 10 : 9; 
                $colIndex++;
            }
        }
        
        // Kolom GRAND TOTAL
        $colLetter = Coordinate::stringFromColumnIndex($colIndex);
        $widths[$colLetter] = 14;

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:'.$lastCol.$lastRow)->getFont()->setName('Arial');

        // 1. Styling Judul (Baris 1)
        $sheet->mergeCells('A1:' . $lastCol . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30); 

        // 2. Merge Header Bulan (Baris 2)
        // Setiap bulan mencakup 13 kolom (12 jenis + 1 TOTAL)
        $colStart = 3; 
        foreach (['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'] as $bln) {
            $colEnd = $colStart + 12; // Tepat 13 kolom (0 sampai 12)
            
            $startLetter = Coordinate::stringFromColumnIndex($colStart);
            $endLetter = Coordinate::stringFromColumnIndex($colEnd);
            
            $sheet->mergeCells($startLetter.'2:'.$endLetter.'2');
            $sheet->setCellValue($startLetter.'2', $bln);
            
            $colStart += 13; // Lompat ke blok bulan berikutnya
        }

        // 3. Merge Vertikal (NO, DIVISION, GRAND TOTAL)
        $sheet->mergeCells('A2:A3'); // NO
        $sheet->mergeCells('B2:B3'); // DIVISION
        $sheet->mergeCells($lastCol.'2:'.$lastCol.'3'); // GRAND TOTAL

        // 4. Styling Header (Baris 2 & 3)
        $sheet->getStyle('A2:'.$lastCol.'3')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => 'A0A8B4']
            ],
            'font' => [
                'bold' => true, 
                'color' => ['rgb' => '111827'],
                'size' => 10 
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER, 
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true 
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ],
        ]);

        // 5. Atur Tinggi Baris Header
        $sheet->getRowDimension(2)->setRowHeight(25); 
        $sheet->getRowDimension(3)->setRowHeight(35); 

        // 6. Styling Isi Data (Baris 4 ke bawah, kecuali baris terakhir)
        $sheet->getStyle('A4:'.$lastCol.($lastRow-1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Kolom Division Rata Kiri
        $sheet->getStyle('B4:B'.($lastRow-1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // 7. Styling Baris Footer / TOTAL Bawah (Baris Terakhir)
        $sheet->getStyle('A'.$lastRow.':'.$lastCol.$lastRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID, 
                'startColor' => ['rgb' => 'A0A8B4']
            ],
            'font' => [
                'bold' => true, 
                'color' => ['rgb' => '000000']
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        // Teks "TOTAL" di footer rata kanan
        $sheet->getStyle('B'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }
}