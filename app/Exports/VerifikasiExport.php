<?php

namespace App\Exports;

use App\Models\Verifikasi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VerifikasiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected string $tahun;
    protected string $mode;
    protected $data;

    public function __construct(string $tahun, string $mode = 'bulanan')
    {
        $this->tahun = $tahun;
        $this->mode = $mode;
    }

    /**
     * Label Tipe untuk Excel:
     * - mode 'bulanan' (kolom Jan-Des)  → "Tahunan"
     * - mode 'harian'  (kolom QA 1, dst) → "Bulanan"
     */
    protected function labelTipe(): string
    {
        return $this->mode === 'bulanan' ? 'Tahunan' : 'Bulanan';
    }

    public function collection()
    {
        $this->data = Verifikasi::where('tahun', $this->tahun)
            ->where('tipe', $this->mode)
            ->orderBy('tool_name', 'asc')
            ->get();

        return $this->data;
    }

    public function headings(): array
    {
        if ($this->mode === 'harian') {
            // Mode 'harian' = BULANAN (kolom QA 1, QA 1.2, dst)
            return [
                'No', 'Tool Name', 'Tipe', 'Tanggal',
                'QA 1', 'QA 1.2', 'QA INC', 'QA INSP',
                'PPIC 1', 'PPIC 2', 'LINE SCC',
                'P1', 'P2', 'P3', 'P5', 'P6', 'P7', 'P8', 'ENG',
                'Total'
            ];
        }

        // Mode 'bulanan' = TAHUNAN (kolom Jan-Des)
        return [
            'No', 'Tool Name', 'Tipe',
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
            'Total'
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        if ($this->mode === 'harian') {
            return [
                $no,
                strtoupper($item->tool_name),
                $this->labelTipe(),  // "Bulanan"
                $item->tanggal,
                (int) ($item->qa_1 ?? 0),
                (int) ($item->qa_1_2 ?? 0),
                (int) ($item->qa_inc ?? 0),
                (int) ($item->qa_insp ?? 0),
                (int) ($item->ppic_1 ?? 0),
                (int) ($item->ppic_2 ?? 0),
                (int) ($item->line_scc ?? 0),
                (int) ($item->p1 ?? 0),
                (int) ($item->p2 ?? 0),
                (int) ($item->p3 ?? 0),
                (int) ($item->p5 ?? 0),
                (int) ($item->p6 ?? 0),
                (int) ($item->p7 ?? 0),
                (int) ($item->p8 ?? 0),
                (int) ($item->eng ?? 0),
                (int) ($item->total ?? 0),
            ];
        }

        return [
            $no,
            strtoupper($item->tool_name),
            $this->labelTipe(),  // "Tahunan"
            (int) ($item->jan ?? 0),
            (int) ($item->feb ?? 0),
            (int) ($item->mar ?? 0),
            (int) ($item->apr ?? 0),
            (int) ($item->may ?? 0),
            (int) ($item->jun ?? 0),
            (int) ($item->jul ?? 0),
            (int) ($item->aug ?? 0),
            (int) ($item->sep ?? 0),
            (int) ($item->oct ?? 0),
            (int) ($item->nov ?? 0),
            (int) ($item->dec ?? 0),
            (int) ($item->total ?? 0),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $jumlahData = $this->data->count();
        $lastCol = $this->mode === 'harian' ? 'T' : 'P';
        $firstDataRow = 2;
        $lastDataRow = $firstDataRow + $jumlahData - 1;

        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        if ($jumlahData > 0) {
            $sheet->getStyle('A1:' . $lastCol . $lastDataRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            $sheet->getStyle('A' . $firstDataRow . ':A' . $lastDataRow)->getAlignment()->setHorizontal('center');

            $firstNumCol = $this->mode === 'harian' ? 'E' : 'D';
            $sheet->getStyle($firstNumCol . $firstDataRow . ':' . $lastCol . $lastDataRow)
                  ->getAlignment()->setHorizontal('center');
            $sheet->getStyle($firstNumCol . $firstDataRow . ':' . $lastCol . $lastDataRow)
                  ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $jumlahData = $this->data->count();
                $firstDataRow = 2;
                $lastDataRow = $firstDataRow + $jumlahData - 1;
                $totalRow = $lastDataRow + 1;
                $lastCol = $this->mode === 'harian' ? 'T' : 'P';
                $firstNumCol = $this->mode === 'harian' ? 'E' : 'D';
                $warnaNol = 'DC2626';

                if ($jumlahData > 0) {
                    for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
                        for ($col = $firstNumCol; $col <= $lastCol; $col++) {
                            $cell = $sheet->getCell($col . $row);
                            $value = $cell->getValue();
                            if ($value === null || $value === '' || $value === 0 || $value === '0') {
                                $cell->setValue(0);
                                $cell->getStyle()->getFont()->getColor()->setRGB($warnaNol);
                            }
                        }
                    }
                }

                if ($this->mode === 'harian') {
                    $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                    $sheet->mergeCells('A' . $totalRow . ':D' . $totalRow);
                    $sheet->setCellValue('E' . $totalRow,  $this->data->sum('qa_1'));
                    $sheet->setCellValue('F' . $totalRow,  $this->data->sum('qa_1_2'));
                    $sheet->setCellValue('G' . $totalRow,  $this->data->sum('qa_inc'));
                    $sheet->setCellValue('H' . $totalRow,  $this->data->sum('qa_insp'));
                    $sheet->setCellValue('I' . $totalRow,  $this->data->sum('ppic_1'));
                    $sheet->setCellValue('J' . $totalRow,  $this->data->sum('ppic_2'));
                    $sheet->setCellValue('K' . $totalRow,  $this->data->sum('line_scc'));
                    $sheet->setCellValue('L' . $totalRow,  $this->data->sum('p1'));
                    $sheet->setCellValue('M' . $totalRow,  $this->data->sum('p2'));
                    $sheet->setCellValue('N' . $totalRow,  $this->data->sum('p3'));
                    $sheet->setCellValue('O' . $totalRow,  $this->data->sum('p5'));
                    $sheet->setCellValue('P' . $totalRow,  $this->data->sum('p6'));
                    $sheet->setCellValue('Q' . $totalRow,  $this->data->sum('p7'));
                    $sheet->setCellValue('R' . $totalRow,  $this->data->sum('p8'));
                    $sheet->setCellValue('S' . $totalRow,  $this->data->sum('eng'));
                    $sheet->setCellValue('T' . $totalRow,  $this->data->sum('total'));
                } else {
                    $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                    $sheet->mergeCells('A' . $totalRow . ':C' . $totalRow);
                    $sheet->setCellValue('D' . $totalRow,  $this->data->sum('jan'));
                    $sheet->setCellValue('E' . $totalRow,  $this->data->sum('feb'));
                    $sheet->setCellValue('F' . $totalRow,  $this->data->sum('mar'));
                    $sheet->setCellValue('G' . $totalRow,  $this->data->sum('apr'));
                    $sheet->setCellValue('H' . $totalRow,  $this->data->sum('may'));
                    $sheet->setCellValue('I' . $totalRow,  $this->data->sum('jun'));
                    $sheet->setCellValue('J' . $totalRow,  $this->data->sum('jul'));
                    $sheet->setCellValue('K' . $totalRow,  $this->data->sum('aug'));
                    $sheet->setCellValue('L' . $totalRow,  $this->data->sum('sep'));
                    $sheet->setCellValue('M' . $totalRow,  $this->data->sum('oct'));
                    $sheet->setCellValue('N' . $totalRow,  $this->data->sum('nov'));
                    $sheet->setCellValue('O' . $totalRow,  $this->data->sum('dec'));
                    $sheet->setCellValue('P' . $totalRow,  $this->data->sum('total'));
                }

                $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
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

                for ($col = $firstNumCol; $col <= $lastCol; $col++) {
                    $cell = $sheet->getCell($col . $totalRow);
                    if ($cell->getValue() == 0) {
                        $cell->getStyle()->getFont()->getColor()->setRGB($warnaNol);
                    }
                }
            },
        ];
    }
}