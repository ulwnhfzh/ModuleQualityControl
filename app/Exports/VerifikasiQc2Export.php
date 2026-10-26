<?php

namespace App\Exports;

use App\Models\VerifikasiQc2;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VerifikasiQc2Export implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function collection()
    {
        $data = VerifikasiQc2::where('tahun', $this->tahun)->orderBy('tool_name')->get();
        
        return $data->map(function($item, $key) {
            return [
                'NO' => $key + 1,
                'TOOL NAME' => $item->tool_name,
                'TYPE' => $item->tool_type,
                'SPECIFICATION' => $item->specification,
                'NUMBER SERI' => $item->serial_number,
                'NUMBER REGISTRATION' => $item->registration_number,
                'SHOP' => $item->shop,
                'VERIFICATION DATE' => $item->period1_verification_date?->format('d/m/Y'),
                'NEXT VERIFICATION DATE' => $item->period1_next_verification_date?->format('d/m/Y'),
                'JGM' => $item->period1_jgm,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NO', 'TOOL NAME', 'TYPE', 'SPECIFICATION', 'NUMBER SERI', 
            'NUMBER REGISTRATION', 'SHOP', 'VERIFICATION DATE', 'NEXT VERIFICATION DATE', 'JGM'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E40AF']]],
        ];
    }
}