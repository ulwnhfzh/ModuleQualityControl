<?php

namespace App\Exports;

use App\Models\DataAllProduksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DataAllProduksiExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $division;

    public function __construct($division)
    {
        $this->division = $division;
    }

    // Menamai Sheet Excel sesuai nama Division
    public function title(): string
    {
        if ($this->division === 'ALL') {
            return 'Semua Division';
        }
        return $this->division;
    }

    public function collection()
    {
        // Jika 'ALL', ambil semua data. Jika tidak, filter berdasarkan division.
        if ($this->division === 'ALL') {
            return DataAllProduksi::orderBy('division', 'asc')->orderBy('id', 'asc')->get();
        }
        
        return DataAllProduksi::where('division', $this->division)->orderBy('id', 'asc')->get();
    }

    public function headings(): array
    {
        $headers = [
            'NO', 'TOOL NAME', 'TYPE', 'SIZE', 'NUMBER SERI', 'NUMBER REG',
            'PART NAME', 'PART NUMBER', 
            'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN',
            'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC', 
            'NOTE'
        ];

        // Jika mengekspor SEMUA division, tambahkan kolom DIVISION di paling kiri setelah NO
        if ($this->division === 'ALL') {
            array_splice($headers, 1, 0, 'DIVISION');
        }

        return $headers;
    }

    public function map($row): array
    {
        static $no = 0; // Nomor urut otomatis
        $no++;
        
        $data = [
            $no,
            $row->tool_name, 
            $row->type, 
            $row->size,
            $row->number_seri, 
            $row->number_reg, 
            $row->part_name, 
            $row->part_number,
            $row->jan, 
            $row->feb, 
            $row->mar, 
            $row->apr, 
            $row->may, 
            $row->jun,
            $row->jul, 
            $row->aug, 
            $row->sep, 
            $row->oct, 
            $row->nov, 
            $row->dec,
            $row->note
        ];

        // Sisipkan nama division jika mengekspor SEMUA
        if ($this->division === 'ALL') {
            array_splice($data, 1, 0, $row->division);
        }

        return $data;
    }
}