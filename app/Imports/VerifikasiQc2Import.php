<?php

namespace App\Imports;

use App\Models\VerifikasiQc2;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class VerifikasiQc2Import implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    protected $tahun;
    protected $rowHeader;

    public function __construct($tahun, $rowHeader = 1)
    {
        $this->tahun = $tahun;
        $this->rowHeader = $rowHeader;
    }

    public function model(array $row)
    {
        if (empty($row['tool_name']) || strtoupper(trim($row['tool_name'])) === 'TOTAL') {
            return null;
        }

        return new VerifikasiQc2([
            'tahun' => $this->tahun,
            'tool_name' => strtoupper(trim($row['tool_name'])),
            'tool_type' => isset($row['type']) ? strtoupper(trim($row['type'])) : null,
            'specification' => $row['specification'] ?? null,
            'serial_number' => $row['number_seri'] ?? null,
            'registration_number' => $row['number_registration'] ?? null,
            'shop' => isset($row['shop']) ? strtoupper(trim($row['shop'])) : null,
            'period1_verification_date' => $this->parseDate($row['verification_date'] ?? null),
            'period1_next_verification_date' => $this->parseDate($row['next_verification_date'] ?? null),
            'period1_jgm' => $row['jgm'] ?? null,
        ]);
    }

    private function parseDate($value)
    {
        if (!$value) return null;
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}