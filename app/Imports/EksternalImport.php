<?php

namespace App\Imports;

use App\Models\Eksternal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EksternalImport implements ToModel, WithHeadingRow
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
        ];
        foreach (SpreadsheetInput::MONTH_FIELDS as $month) {
            $data[$month] = SpreadsheetInput::integer($row[$month] ?? null, "{$row['tool_name']} / {$month}");
        }

        $data['qty_set'] = array_sum([
            $data['jan'], $data['feb'], $data['mar'], $data['apr'],
            $data['may'], $data['jun'], $data['jul'], $data['aug'],
            $data['sep'], $data['oct'], $data['nov'], $data['dec']
        ]);

        return new Eksternal($data);
    }
}
