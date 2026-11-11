<?php

namespace App\Exports;

use App\Models\InternalVerificationSchedule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class VerificationPeriodExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithTitle
{
    private int $number = 0;

    public function __construct(private readonly int $tahun, private readonly string $company = 'HAMATETSU')
    {
    }

    public function title(): string { return 'Verification Periode'; }

    public function collection(): Collection
    {
        return InternalVerificationSchedule::where('tahun', $this->tahun)->where('company', $this->company)->orderBy('tool_name')->get();
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
