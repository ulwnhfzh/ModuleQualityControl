<?php

namespace App\Imports;

use App\Models\EksternalMesin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MesinImport implements ToModel, WithHeadingRow
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
            'tool_name'      => strtoupper($row['tool_name']),
            'machine_model'  => $row['machine_model'] ?? null,
            'code_number'    => $row['code_number'] ?? null,
            'serial_number'  => $row['serial_number'] ?? null,
            'maker'          => $row['maker'] ?? null,
            'no_machine'     => $row['no_machine'] ?? null,
            'retension'      => $row['retension'] ?? null,
            'start_date'     => SpreadsheetInput::date($row['start_date'] ?? null, "{$row['tool_name']} / start_date"),
            'next_date'      => SpreadsheetInput::date($row['next_date'] ?? null, "{$row['tool_name']} / next_date"),
            'tahun'          => $this->tahun,
        ];
        foreach (SpreadsheetInput::MONTH_FIELDS as $month) {
            $data[$month] = SpreadsheetInput::integer($row[$month] ?? null, "{$row['tool_name']} / {$month}");
        }

        $data['total'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'],
            $data['may'], $data['jun'], $data['jul'], $data['aug'],
            $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        return new EksternalMesin($data);
    }
}
