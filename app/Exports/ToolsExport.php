<?php

namespace App\Exports;

use App\Models\Tool;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ToolsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected string $tahun;
    protected string $kategori;
    protected $tools;

    public function __construct(string $tahun, string $kategori)
    {
        $this->tahun = $tahun;
        $this->kategori = $kategori;
    }

    public function collection()
    {
        $this->tools = Tool::where('tahun', $this->tahun)
            ->where('category', $this->kategori)
            ->orderBy('tool_name', 'asc')
            ->get();

        return $this->tools;
    }

    public function headings(): array
    {
        return [
            'No', 'Tool Name', 'Category', 'Tahun',
            'QA 1', 'QA 1.2', 'QA 2', 'QA INC', 'QA INSP',
            'PPIC 1', 'PPIC 2', 'LINE SCC',
            'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'ENG',
            'Total'
        ];
    }

    public function map($tool): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            strtoupper($tool->tool_name),
            $tool->category,
            $tool->tahun,
            (int) ($tool->qa_1 ?? 0),
            (int) ($tool->qa_1_2 ?? 0),
            (int) ($tool->qa_2 ?? 0),
            (int) ($tool->qa_inc ?? 0),
            (int) ($tool->qa_insp ?? 0),
            (int) ($tool->ppic_1 ?? 0),
            (int) ($tool->ppic_2 ?? 0),
            (int) ($tool->line_scc ?? 0),
            (int) ($tool->p1 ?? 0),
            (int) ($tool->p2 ?? 0),
            (int) ($tool->p3 ?? 0),
            (int) ($tool->p5 ?? 0),
            (int) ($tool->p6 ?? 0),
            (int) ($tool->p7 ?? 0),
            (int) ($tool->p8 ?? 0),
            (int) ($tool->eng ?? 0),
            (int) ($tool->total ?? 0),
        ];
    }

    /**
     * Style dasar (header + border + alignment)
     * CATATAN: Jumlah baris data = $this->tools->count()
     * Baris data = 2 s/d (1 + jumlah data)
     */
    public function styles(Worksheet $sheet)
    {
        $jumlahData = $this->tools->count();
        $firstDataRow = 2;
        $lastDataRow = $firstDataRow + $jumlahData - 1;
        $lastCol = 'U';

        // ===== HEADER (baris 1) =====
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ===== BORDER SELURUH TABEL =====
        if ($jumlahData > 0) {
            $sheet->getStyle('A1:' . $lastCol . $lastDataRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // ===== ALIGNMENT & FORMAT DATA =====
        if ($jumlahData > 0) {
            // Kolom No rata tengah
            $sheet->getStyle('A' . $firstDataRow . ':A' . $lastDataRow)
                  ->getAlignment()->setHorizontal('center');

            // Kolom angka (E s/d U) rata tengah + format angka
            $sheet->getStyle('E' . $firstDataRow . ':' . $lastCol . $lastDataRow)
                  ->getAlignment()->setHorizontal('center');
            $sheet->getStyle('E' . $firstDataRow . ':' . $lastCol . $lastDataRow)
                  ->getNumberFormat()
                  ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $jumlahData = $this->tools->count();
                $firstDataRow = 2;
                $lastDataRow = $firstDataRow + $jumlahData - 1;
                $totalRow = $lastDataRow + 1; // Baris TOTAL = tepat setelah data terakhir

                // ===== WARNAI ANGKA 0 DI DATA =====
                // Warna: '999999' = abu-abu, 'DC2626' = merah, 'F59E0B' = oranye
                $warnaNol = 'DC2626'; // Merah

                if ($jumlahData > 0) {
                    for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
                        for ($col = 'E'; $col <= 'U'; $col++) {
                            $cell = $sheet->getCell($col . $row);
                            $value = $cell->getValue();

                            if ($value === null || $value === '' || $value === 0 || $value === '0') {
                                $cell->setValue(0);
                                $cell->getStyle()->getFont()->getColor()->setRGB($warnaNol);
                            }
                        }
                    }
                }

                // ===== BARIS TOTAL =====
                $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);

                $sheet->setCellValue('E' . $totalRow,  $this->tools->sum('qa_1'));
                $sheet->setCellValue('F' . $totalRow,  $this->tools->sum('qa_1_2'));
                $sheet->setCellValue('G' . $totalRow,  $this->tools->sum('qa_2'));
                $sheet->setCellValue('H' . $totalRow,  $this->tools->sum('qa_inc'));
                $sheet->setCellValue('I' . $totalRow,  $this->tools->sum('qa_insp'));
                $sheet->setCellValue('J' . $totalRow,  $this->tools->sum('ppic_1'));
                $sheet->setCellValue('K' . $totalRow,  $this->tools->sum('ppic_2'));
                $sheet->setCellValue('L' . $totalRow,  $this->tools->sum('line_scc'));
                $sheet->setCellValue('M' . $totalRow,  $this->tools->sum('p1'));
                $sheet->setCellValue('N' . $totalRow,  $this->tools->sum('p2'));
                $sheet->setCellValue('O' . $totalRow,  $this->tools->sum('p3'));
                $sheet->setCellValue('P' . $totalRow,  $this->tools->sum('p5'));
                $sheet->setCellValue('Q' . $totalRow,  $this->tools->sum('p6'));
                $sheet->setCellValue('R' . $totalRow,  $this->tools->sum('p7'));
                $sheet->setCellValue('S' . $totalRow,  $this->tools->sum('p8'));
                $sheet->setCellValue('T' . $totalRow,  $this->tools->sum('eng'));
                $sheet->setCellValue('U' . $totalRow,  $this->tools->sum('total'));

                // STYLE BARIS TOTAL
                $sheet->getStyle('A' . $totalRow . ':U' . $totalRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '065F46'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D1FAE5'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal('left');

                // ===== WARNA ANGKA 0 DI BARIS TOTAL =====
                // Kalau Anda mau angka 0 di baris TOTAL juga berwarna abu-abu
                for ($col = 'E'; $col <= 'U'; $col++) {
                    $cell = $sheet->getCell($col . $totalRow);
                    if ($cell->getValue() == 0) {
                        $cell->getStyle()->getFont()->getColor()->setRGB($warnaNol);
                    }
                }

                DocumentExportLayout::apply($sheet, 'U', 'DAFTAR INDUK ALAT UKUR - ' . $this->kategori, $this->tahun);
            },
        ];
    }
}
