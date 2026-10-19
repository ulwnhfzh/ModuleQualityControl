<?php

namespace App\Exports;

use App\Models\KalibrasiInternalListUp;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class KalibrasiInternalListUpExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    public function __construct(private readonly int $tahun) {}

    public function collection(): Collection
    {
        return KalibrasiInternalListUp::where('tahun', $this->tahun)->orderBy('tool_name')->get();
    }

    public function headings(): array
    {
        return ['No', 'Tool Name', 'Type', 'Specification', 'Number Seri', 'Number Registration', 'Shop', 'Calibration Date', 'Next Calibration Date', 'JGM'];
    }

    public function map($row): array
    {
        static $number = 0;
        return [++$number, $row->tool_name, $row->tool_type, $row->specification, $row->serial_number,
            $row->registration_number, $row->shop, $row->calibration_date?->format('d F Y'),
            $row->next_calibration_date?->format('d F Y'), $row->jgm];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            DocumentExportLayout::apply($event->sheet->getDelegate(), 'J', 'JADWAL KALIBRASI INTERNAL', (string) $this->tahun);
        }];
    }
}
