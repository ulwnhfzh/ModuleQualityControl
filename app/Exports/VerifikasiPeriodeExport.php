<?php

namespace App\Exports;

use App\Models\VerifikasiInternalSchedule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class VerifikasiPeriodeExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    private int $number = 0;

    public function __construct(private readonly int $tahun)
    {
    }

    public function collection(): Collection
    {
        return VerifikasiInternalSchedule::where('tahun', $this->tahun)->orderBy('tool_name')->get();
    }

    public function headings(): array
    {
        return [
            'tool_name', 'tool_type', 'specification', 'serial_number', 'registration_number', 'shop',
            'period1_verification_date', 'period1_next_verification_date', 'period1_jgm',
            'period2_verification_date', 'period2_next_verification_date', 'period2_jgm',
        ];
    }

    public function map($row): array
    {
        return [
            $row->tool_name,
            $row->tool_type,
            $row->specification,
            $row->serial_number,
            $row->registration_number,
            $row->shop,
            $row->period1_verification_date?->format('Y-m-d'),
            $row->period1_next_verification_date?->format('Y-m-d'),
            $row->period1_jgm,
            $row->period2_verification_date?->format('Y-m-d'),
            $row->period2_next_verification_date?->format('Y-m-d'),
            $row->period2_jgm,
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            DocumentExportLayout::apply($event->sheet->getDelegate(), 'L', 'JADWAL VERIFIKASI INTERNAL', (string) $this->tahun);
        }];
    }
}
