<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly Collection $logs,
        private readonly string $title,
        private readonly string $documentDetails,
        private readonly string $dateFormat
    ) {
    }

    public function collection(): Collection
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return ['No', 'Waktu', 'User', 'Aksi', 'Jenis Data', 'Keterangan', 'IP Address'];
    }

    public function map($log): array
    {
        return [
            ++$this->rowNumber,
            $log->created_at->format($this->dateFormat),
            $log->user_name ?? '-',
            $log->aksi,
            $log->label_jenis ?? '-',
            $log->keterangan ?? '-',
            $log->ip_address ?? '-',
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            DocumentExportLayout::apply($sheet, 'G', $this->title);
            $sheet->mergeCells('B7:E7');
            $sheet->mergeCells('F7:H7');
            $sheet->setCellValue('B7', $this->documentDetails);
            $sheet->getStyle('B7:H7')->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '172033']],
                'alignment' => ['vertical' => 'center', 'wrapText' => true],
            ]);
        }];
    }
}