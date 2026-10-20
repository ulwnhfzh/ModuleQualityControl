<?php

namespace App\Imports;

use App\Models\ToolDefect;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ToolDefectsImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    protected $tahun;
    protected $bulan;

    public function __construct($tahun, $bulan)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
    }

    public function model(array $row)
    {
        // Abaikan baris kosong atau baris TOTAL di bagian bawah Excel
        $division = mb_strtoupper(trim((string) ($row['division'] ?? '')));
        if ($division === '' || $division === 'TOTAL') {
            return null;
        }

        if (!in_array($division, ToolDefect::DIVISIONS, true)) {
            throw new \RuntimeException("Divisi '{$division}' tidak terdaftar pada format Data NG.");
        }

        $data = [
            'tahun'     => $this->tahun,
            'bulan'     => $this->bulan,
            'division'  => $division,
        ];
        foreach (ToolDefect::COUNT_FIELDS as $field) {
            $data[$field] = SpreadsheetInput::integer($row[$field] ?? null, "{$division} / {$field}");
        }

        return new ToolDefect($data);
    }
}