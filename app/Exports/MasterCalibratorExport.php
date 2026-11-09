<?php

namespace App\Exports;

use App\Models\MasterCalibrator;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MasterCalibratorExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(private int $year) {}
    public function title(): string { return 'Master Kalibrator'; }
    public function collection() { return MasterCalibrator::where('tahun', $this->year)->orderBy('tool_name')->orderBy('size')->get(); }
    public function headings(): array
    {
        return ['NO','TOOL NAME','TYPE','SIZE','NUMBER SERI','NUMBER REG','PART NAME','PART NUMBER','JAN','FEB','MAR','APR','MEI','JUN','JUL','AGS','SEP','OKT','NOV','DES','NOTE'];
    }
    public function map($item): array
    {
        static $number = 0;
        return [++$number, $item->tool_name, $item->type, $item->size, $item->number_seri, $item->number_reg, $item->part_name, $item->part_number,
            $item->jan, $item->feb, $item->mar, $item->apr, $item->may, $item->jun, $item->jul, $item->aug, $item->sep, $item->oct, $item->nov, $item->dec, $item->note];
    }
}
