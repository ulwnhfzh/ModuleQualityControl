<?php

namespace App\Imports;

use App\Models\Verifikasi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VerifikasiImport implements ToModel, WithHeadingRow
{
    protected string $tahun;
    protected string $mode;

    public function __construct(string $tahun, string $mode)
    {
        $this->tahun = $tahun;
        $this->mode = $mode;
    }

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
            $data['tanggal']  = $row['tanggal'] ?? null;
            $data['qa_1']     = $row['qa_1'] ?? 0;
            $data['qa_1_2']   = $row['qa_1_2'] ?? 0;
            $data['qa_inc']   = $row['qa_inc'] ?? 0;
            $data['qa_insp']  = $row['qa_insp'] ?? 0;
            $data['ppic_1']   = $row['ppic_1'] ?? 0;
            $data['ppic_2']   = $row['ppic_2'] ?? 0;
            $data['line_scc'] = $row['line_scc'] ?? 0;
            $data['p1']       = $row['p1'] ?? 0;
            $data['p2']       = $row['p2'] ?? 0;
            $data['p3']       = $row['p3'] ?? 0;
            $data['p5']       = $row['p5'] ?? 0;
            $data['p6']       = $row['p6'] ?? 0;
            $data['p7']       = $row['p7'] ?? 0;
            $data['p8']       = $row['p8'] ?? 0;
            $data['eng']      = $row['eng'] ?? 0;

            $data['total'] = array_sum([
                $data['qa_1'], $data['qa_1_2'], $data['qa_inc'], $data['qa_insp'],
                $data['ppic_1'], $data['ppic_2'], $data['line_scc'],
                $data['p1'], $data['p2'], $data['p3'], $data['p5'],
                $data['p6'], $data['p7'], $data['p8'], $data['eng']
            ]);
        } else {
            $data['jan'] = $row['jan'] ?? 0;
            $data['feb'] = $row['feb'] ?? 0;
            $data['mar'] = $row['mar'] ?? 0;
            $data['apr'] = $row['apr'] ?? 0;
            $data['may'] = $row['may'] ?? 0;
            $data['jun'] = $row['jun'] ?? 0;
            $data['jul'] = $row['jul'] ?? 0;
            $data['aug'] = $row['aug'] ?? 0;
            $data['sep'] = $row['sep'] ?? 0;
            $data['oct'] = $row['oct'] ?? 0;
            $data['nov'] = $row['nov'] ?? 0;
            $data['dec'] = $row['dec'] ?? 0;

            $data['total'] = array_sum([
                $data['jan'], $data['feb'], $data['mar'], $data['apr'],
                $data['may'], $data['jun'], $data['jul'], $data['aug'],
                $data['sep'], $data['oct'], $data['nov'], $data['dec']
            ]);
        }

        return new Verifikasi($data);
    }
}