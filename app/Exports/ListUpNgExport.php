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
    protected $division;
    protected $categoryNg;
    protected $bulan;

    public function __construct($tahun, $division = null, $categoryNg = null, $bulan = null)
    {
        $this->tahun = $tahun;
        $this->division = $division;
        $this->categoryNg = $categoryNg;
        $this->bulan = $bulan;
    }

    public function collection()
    {
        $query = ListUpNg::where('tahun', $this->tahun);

        if ($this->bulan) {
            $query->whereMonth('ng_date', $this->bulan);
        }

        if ($this->division) {
            $query->where('division', $this->division);
        }

        if ($this->categoryNg) {
            $query->where('category_ng', $this->categoryNg);
        }

        return $query->orderBy('ng_date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG.',
            'PART NAME', 'PART NUMBER', 'NG DATE', 'DIVISION', 'CATEGORY NG', 'NOTE',
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
            $row->division ?? $row->division,
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