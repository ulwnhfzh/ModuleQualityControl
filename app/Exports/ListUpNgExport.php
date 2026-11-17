<?php

namespace App\Exports;

use App\Models\ListUpNg;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ListUpNgExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $tahun;
    protected $dept;

    public function __construct($tahun, $dept = null)
    {
        $this->tahun = $tahun;
        $this->dept = $dept;
    }

    public function collection()
    {
        $query = ListUpNg::where('tahun', $this->tahun);
        if ($this->dept) {
            $query->where('dept', $this->dept);
        }
        return $query->orderBy('ng_date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG.',
            'PART NAME', 'PART NUMBER', 'NG DATE', 'DEPT.', 'CATEGORY NG', 'NOTE'
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            strtoupper($row->tool_name),
            $row->type ?? '-',
            $row->size ?? '-',
            $row->number_seri ?? '-',
            $row->number_reg ?? '-',
            $row->part_name ?? '-',
            $row->part_number ?? '-',
            $row->ng_date ? $row->ng_date->format('d/m/Y') : '-',
            $row->dept,
            $row->category_ng,
            $row->note ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000'],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
    }
}