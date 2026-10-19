<?php

namespace App\Imports;

use App\Models\Internal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InternalImport implements ToModel, WithHeadingRow
{
    protected string $tahun;
    protected int $rowHeader;

    public function __construct(string $tahun, int $rowHeader = 1)
    {
        $this->tahun = $tahun;
        $this->rowHeader = $rowHeader;
    }

    public function headingRow(): int { return $this->rowHeader; }

    public function model(array $row)
    {
        if (empty($row['tool_name'])) {
            return null;
        }

        $data = [
            'tool_name' => strtoupper($row['tool_name']),
            'tahun'     => $this->tahun,
            'jan'       => $row['jan'] ?? 0,
            'feb'       => $row['feb'] ?? 0,
            'mar'       => $row['mar'] ?? 0,
            'apr'       => $row['apr'] ?? 0,
            'may'       => $row['may'] ?? 0,
            'jun'       => $row['jun'] ?? 0,
            'jul'       => $row['jul'] ?? 0,
            'aug'       => $row['aug'] ?? 0,
            'sep'       => $row['sep'] ?? 0,
            'oct'       => $row['oct'] ?? 0,
            'nov'       => $row['nov'] ?? 0,
            'dec'       => $row['dec'] ?? 0,
        ];

        $data['qty'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'],
            $data['may'], $data['jun'], $data['jul'], $data['aug'],
            $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        return new Internal($data);
    }
}
