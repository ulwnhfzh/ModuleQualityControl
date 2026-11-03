<?php

namespace App\Exports;

use App\Models\DataAllProduksi;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataAllProduksiExport implements FromQuery, WithHeadings, WithMapping, WithTitle, WithColumnWidths, WithStyles, WithEvents
{
    protected $division;
    private int $rowNumber = 0;

    public function __construct($division)
    {
        $this->division = $division;
    }

    // Menamai Sheet Excel sesuai nama Division
    public function title(): string
    {
        if ($this->division === 'ALL') {
            return 'Data All Produksi';
        }
        return $this->division;
    }

    public function query(): Builder
    {
        // Jika 'ALL', ambil semua data. Jika tidak, filter berdasarkan division.
        if ($this->division === 'ALL') {
            return DataAllProduksi::query()->orderBy('division', 'asc')->orderBy('id', 'asc');
        }
        
        return DataAllProduksi::query()->where('division', $this->division)->orderBy('id', 'asc');
    }

    public function headings(): array
    {
        $headers = [
            'NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG',
            'PART NAME', 'PART NUMBER', 
            'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN',
            'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 
            'NOTE'
        ];

        // Jika mengekspor SEMUA division, tambahkan kolom DIVISION di paling kiri setelah NO
        if ($this->division === 'ALL') {
            array_splice($headers, 1, 0, 'DIVISION');
        }

        return $headers;
    }

    public function map($row): array
    {
        $no = ++$this->rowNumber;
        
        $data = [
            $no,
            $row->tool_name, 
            $row->type, 
            $row->size,
            $row->number_seri, 
            $row->number_reg, 
            $row->part_name, 
            $row->part_number,
            $row->jan, 
            $row->feb, 
            $row->mar, 
            $row->apr, 
            $row->may, 
            $row->jun,
            $row->jul, 
            $row->aug, 
            $row->sep, 
            $row->oct, 
            $row->nov, 
            $row->dec,
            $row->note
        ];

        // Sisipkan nama division jika mengekspor SEMUA
        if ($this->division === 'ALL') {
            array_splice($data, 1, 0, $row->division);
        }

        return $data;
    }

    public function columnWidths(): array
    {
        if ($this->division === 'ALL') {
            return [
                'A' => 7, 'B' => 18, 'C' => 30, 'D' => 18, 'E' => 18,
                'F' => 20, 'G' => 20, 'H' => 26, 'I' => 28,
                'J' => 11, 'K' => 11, 'L' => 11, 'M' => 11, 'N' => 11, 'O' => 11,
                'P' => 11, 'Q' => 11, 'R' => 11, 'S' => 11, 'T' => 11, 'U' => 11,
                'V' => 36,
            ];
        }

        return [
            'A' => 7, 'B' => 30, 'C' => 18, 'D' => 18, 'E' => 20,
            'F' => 20, 'G' => 26, 'H' => 28,
            'I' => 11, 'J' => 11, 'K' => 11, 'L' => 11, 'M' => 11, 'N' => 11,
            'O' => 11, 'P' => 11, 'Q' => 11, 'R' => 11, 'S' => 11, 'T' => 11,
            'U' => 36,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E1']]],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
            $lastRow = max(1, $sheet->getHighestRow());

            $sheet->freezePane($this->division === 'ALL' ? 'D2' : 'C2');
            $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
            $sheet->getRowDimension(1)->setRowHeight(32);
            if ($lastRow > 1) {
                $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }];
    }
}
