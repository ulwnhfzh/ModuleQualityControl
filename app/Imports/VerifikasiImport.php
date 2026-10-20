<?php

namespace App\Imports;

use App\Models\Verifikasi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VerifikasiImport implements ToModel, WithHeadingRow
{
    protected string $tahun;
    protected string $mode;
    protected int $rowHeader;

    public function __construct(string $tahun, string $mode, int $rowHeader = 1)
    {
        $this->tahun = $tahun;
        $this->mode = $mode;
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
            'tipe'      => $this->mode,
        ];

        if ($this->mode === 'harian') {
            $data['tanggal'] = SpreadsheetInput::date($row['tanggal'] ?? null, "{$row['tool_name']} / tanggal");
            $fields = ['qa_1', 'qa_1_2', 'qa_inc', 'qa_insp', 'ppic_1', 'ppic_2', 'line_scc', 'p1', 'p2', 'p3', 'p5', 'p6', 'p7', 'p8', 'eng'];
            foreach ($fields as $field) {
                $data[$field] = SpreadsheetInput::integer($row[$field] ?? null, "{$row['tool_name']} / {$field}");
            }

            $data['total'] = array_sum([
                $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
                $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
                $data['p1'], $data['p2'], $data['p3'], $data['p5'],
                $data['p6'], $data['p7'], $data['p8'], $data['eng']
            ]);
        } else {
            foreach (SpreadsheetInput::MONTH_FIELDS as $month) {
                $data[$month] = SpreadsheetInput::integer($row[$month] ?? null, "{$row['tool_name']} / {$month}");
            }

            $data['total'] = array_sum([
                $data['jan'], $data['feb'], $data['mar'], $data['apr'],
                $data['may'], $data['jun'], $data['jul'], $data['aug'],
                $data['sep'], $data['oct'], $data['nov'], $data['dec']
            ]);
        }

        return new Verifikasi($data);
    }
}
