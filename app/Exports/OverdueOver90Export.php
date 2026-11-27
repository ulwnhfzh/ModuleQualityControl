<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OverdueOver90Export implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $rows)
    {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Jenis', 'Nama Alat', 'Detail', 'Jatuh Tempo', 'Hari Terlambat'];
    }

    public function title(): string
    {
        return 'Terlambat lebih 90 hari';
    }
}