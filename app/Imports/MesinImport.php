<?php

namespace App\Imports;

use App\Models\EksternalMesin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MesinImport implements ToModel, WithHeadingRow
{
    protected string $tahun;

    public function __construct(string $tahun)
    {
        $this->tahun = $tahun;
    }

    public function model(array $row)
    {
        if (empty($row['tool_name'])) {
            return null;
        }

        $data = [
            'tool_name'      => strtoupper($row['tool_name']),
            'machine_model'  => $row['machine_model'] ?? null,
            'code_number'    => $row['code_number'] ?? null,
            'serial_number'  => $row['serial_number'] ?? null,
            'maker'          => $row['maker'] ?? null,
            'no_machine'     => $row['no_machine'] ?? null,
            'retension'      => $row['retension'] ?? null,
            'start_date'     => $row['start_date'] ?? null,
            'next_date'      => $row['next_date'] ?? null,
            'tahun'          => $this->tahun,
            'jan' => $row['jan'] ?? 0,
            'feb' => $row['feb'] ?? 0,
            'mar' => $row['mar'] ?? 0,
            'apr' => $row['apr'] ?? 0,
            'may' => $row['may'] ?? 0,
            'jun' => $row['jun'] ?? 0,
            'jul' => $row['jul'] ?? 0,
            'aug' => $row['aug'] ?? 0,
            'sep' => $row['sep'] ?? 0,
            'oct' => $row['oct'] ?? 0,
            'nov' => $row['nov'] ?? 0,
            'dec' => $row['dec'] ?? 0,
        ];

        $data['total'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'],
            $data['may'], $data['jun'], $data['jul'], $data['aug'],
            $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        return new EksternalMesin($data);
    }
}