<?php

namespace App\Imports;

use App\Models\Tool;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class ToolsImport implements ToModel, WithHeadingRow, WithUpserts
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
            'tool_name' => strtoupper($row['tool_name']),
            'category'  => $row['category'] ?? 'Small Tool',
            'tahun'     => $this->tahun,
            'qa_1'      => $row['qa_1'] ?? 0,
            'qa_1_2'    => $row['qa_1_2'] ?? 0,
            'qa_inc'    => $row['qa_inc'] ?? 0,
            'qa_insp'   => $row['qa_insp'] ?? 0,
            'ppic_1'    => $row['ppic_1'] ?? 0,
            'ppic_2'    => $row['ppic_2'] ?? 0,
            'line_scc'  => $row['line_scc'] ?? 0,
            'p1'        => $row['p1'] ?? 0,
            'p2'        => $row['p2'] ?? 0,
            'p3'        => $row['p3'] ?? 0,
            'p5'        => $row['p5'] ?? 0,
            'p6'        => $row['p6'] ?? 0,
            'p7'        => $row['p7'] ?? 0,
            'p8'        => $row['p8'] ?? 0,
            'eng'       => $row['eng'] ?? 0,
        ];

        $data['total'] = array_sum([
            $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
            $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
            $data['p1'], $data['p2'], $data['p3'], $data['p5'],
            $data['p6'], $data['p7'], $data['p8'], $data['eng']
        ]);

        return new Tool($data);
    }

    public function uniqueBy()
    {
        return 'tool_name';
    }
}