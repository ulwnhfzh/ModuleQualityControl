<?php

namespace App\Exports;

use App\Models\ExternalCalibrationListUp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExternalCalibrationListUpExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(private readonly int $year) {}
    public function title(): string { return 'External Calibration'; }
    public function collection() { return ExternalCalibrationListUp::where('tahun', $this->year)->orderBy('tool_name')->orderBy('registration_number')->get(); }
    public function headings(): array
    {
        return ['NO','TOOL NAME','SPECIFICATION','NUMBER SERI','NUMBER REGISTRATION','SHOP','CALIBRATION DATE','NEXT CALIBRATION DATE','JGM','NUMBER KALIBRASI','NOTE'];
    }
    public function map($item): array
    {
        static $number = 0;
        return [++$number, $item->tool_name, $item->specification, $item->serial_number, $item->registration_number,
            $item->shop, $item->calibration_date?->format('d F Y'), $item->next_calibration_date?->format('d F Y'),
            $item->jgm, $item->calibration_count, $item->note];
    }
}
