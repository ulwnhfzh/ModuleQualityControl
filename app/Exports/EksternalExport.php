<?php

namespace App\Exports;

use App\Models\Eksternal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EksternalExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected string $tahun;
    protected $data;

    public function __construct(string $tahun)
    {
        $this->tahun = $tahun;
    }

    public function collection()
    {
        $this->data = Eksternal::where('tahun', $this->tahun)
            ->orderBy('tool_name', 'asc')
            ->get();

        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No', 'Tool Name',
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
            'Qty Set'
        ];
    }

    public function map($item): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            strtoupper($item->tool_name),
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
            (int) ($item->qty_set ?? 0),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $jumlahData = $this->data->count();
        $lastCol = 'O';
        $firstDataRow = 2;
        $lastDataRow = $firstDataRow + $jumlahData - 1;

        // HEADER
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

            // Kolom angka = C s/d O
            $sheet->getStyle('C' . $firstDataRow . ':' . $lastCol . $lastDataRow)
                  ->getAlignment()->setHorizontal('center');
            $sheet->getStyle('C' . $firstDataRow . ':' . $lastCol . $lastDataRow)
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
                $lastCol = 'O';
                $warnaNol = 'DC2626';

                if ($jumlahData > 0) {
                    for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
                        for ($col = 'C'; $col <= $lastCol; $col++) {
                            $cell = $sheet->getCell($col . $row);
                            $value = $cell->getValue();
                            if ($value === null || $value === '' || $value === 0 || $value === '0') {
                                $cell->setValue(0);
                                $cell->getStyle()->getFont()->getColor()->setRGB($warnaNol);
                            }
                        }
                    }
                }

                // BARIS TOTAL
                $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                $sheet->mergeCells('A' . $totalRow . ':B' . $totalRow);
                $sheet->setCellValue('C' . $totalRow,  $this->data->sum('jan'));
                $sheet->setCellValue('D' . $totalRow,  $this->data->sum('feb'));
                $sheet->setCellValue('E' . $totalRow,  $this->data->sum('mar'));
                $sheet->setCellValue('F' . $totalRow,  $this->data->sum('apr'));
                $sheet->setCellValue('G' . $totalRow,  $this->data->sum('may'));
                $sheet->setCellValue('H' . $totalRow,  $this->data->sum('jun'));
                $sheet->setCellValue('I' . $totalRow,  $this->data->sum('jul'));
                $sheet->setCellValue('J' . $totalRow,  $this->data->sum('aug'));
                $sheet->setCellValue('K' . $totalRow,  $this->data->sum('sep'));
                $sheet->setCellValue('L' . $totalRow,  $this->data->sum('oct'));
                $sheet->setCellValue('M' . $totalRow,  $this->data->sum('nov'));
                $sheet->setCellValue('N' . $totalRow,  $this->data->sum('dec'));
                $sheet->setCellValue('O' . $totalRow,  $this->data->sum('qty_set'));

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

                for ($col = 'C'; $col <= $lastCol; $col++) {
                    $cell = $sheet->getCell($col . $totalRow);
                    if ($cell->getValue() == 0) {
                        $cell->getStyle()->getFont()->getColor()->setRGB($warnaNol);
                    }
                }
            },
        ];
    }
}