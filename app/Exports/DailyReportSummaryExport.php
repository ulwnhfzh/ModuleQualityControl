<?php

namespace App\Exports;

use Carbon\CarbonInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyReportSummaryExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        private readonly CarbonInterface $tanggal,
        private readonly string $nomorDokumen,
        private readonly string $pencetak,
        private readonly int $totalAktivitas,
        private readonly int $verifikasi,
        private readonly int $kalibrasi,
        private readonly Collection $aktivitasPerJenis,
        private readonly Collection $alatJatuhTempo,
        private readonly int $warningDays,
    ) {
    }

    public function headings(): array
    {
        return ['Bagian', 'Keterangan', 'Jumlah / Tanggal', 'Catatan'];
    }

    public function array(): array
    {
        $rows = [
            ['Dokumen', 'Tanggal laporan', $this->tanggal->format('d/m/Y'), ''],
            ['Dokumen', 'Nomor dokumen', $this->nomorDokumen, ''],
            ['Dokumen', 'Dicetak oleh', $this->pencetak, ''],
            ['Ringkasan', 'Total aktivitas', $this->totalAktivitas, ''],
            ['Ringkasan', 'Input/update verifikasi', $this->verifikasi, ''],
            ['Ringkasan', 'Input/update kalibrasi', $this->kalibrasi, ''],
            ['Aktivitas per fitur', '', '', ''],
        ];

        $labels = [
            'tools' => 'Daftar Induk', 'verifikasi' => 'Verification', 'internal' => 'Kalibrasi Internal',
            'eksternal' => 'External Small', 'mesin' => 'External Machine', 'user' => 'User Management', 'laporan' => 'Laporan',
        ];
        foreach ($this->aktivitasPerJenis as $item) {
            $rows[] = ['Aktivitas per fitur', $labels[$item->jenis_data] ?? $item->jenis_data ?? '-', (int) $item->jumlah, ''];
        }

        $rows[] = ['Tenggat kalibrasi', "Dalam {$this->warningDays} hari ke depan", '', ''];
        foreach ($this->alatJatuhTempo as $tool) {
            $rows[] = [
                'Tenggat kalibrasi',
                $tool->tool_name,
                $tool->next_date ? Carbon::parse($tool->next_date)->format('d/m/Y') : '-',
                $tool->sisa_hari < 0 ? 'Terlambat ' . abs($tool->sisa_hari) . ' hari' : $tool->sisa_hari . ' hari lagi',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Ringkasan Harian';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E40AF']]],
        ];
    }
}
