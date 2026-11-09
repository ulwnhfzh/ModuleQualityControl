<?php

namespace App\Imports;

use App\Models\Verifikasi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VerifikasiImport implements ToModel, WithHeadingRow
{
    protected string $tahun;
    protected string $mode;
    protected string $company;
    protected int $rowHeader;
    protected array $plannedNissoSchedules = [];
    protected array $initialNissoSchedules = [];

    public function __construct(string $tahun, string $mode, string $company = 'HAMATETSU', int $rowHeader = 1)
    {
        $this->tahun = $tahun;
        $this->mode = $mode;
        $this->company = $company;
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
            'company'   => $this->company,
        ];

        if ($this->mode === 'harian') {
            $data['tanggal'] = SpreadsheetInput::date($row['tanggal'] ?? null, "{$row['tool_name']} / tanggal");
            if ($this->company === 'NISSO GIKEN') {
                $this->validateNissoSchedule($data);
            }
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

    private function validateNissoSchedule(array $data): void
    {
        if (empty($data['tanggal'])) {
            throw new \RuntimeException("{$data['tool_name']}: tanggal jadwal NISSO GIKEN wajib diisi.");
        }

        $name = strtoupper($data['tool_name']);
        if (!array_key_exists($name, $this->initialNissoSchedules)) {
            $this->initialNissoSchedules[$name] = Verifikasi::query()
                ->where('company', 'NISSO GIKEN')->where('tipe', 'harian')
                ->where('tool_name', $name)->whereNotNull('tanggal')
                ->get(['tahun', 'tanggal'])->map(fn ($record) => ['tahun' => (int) $record->tahun, 'tanggal' => (string) $record->tanggal])->all();
        }
        $existing = $this->initialNissoSchedules[$name];
        $planned = $this->plannedNissoSchedules[$name] ?? [];
        $sameYearCount = count(array_filter($existing, fn ($record) => $record['tahun'] === (int) $this->tahun))
            + count(array_filter($planned, fn ($date) => (int) substr($date, 0, 4) === (int) $this->tahun));
        if ($sameYearCount >= 2) {
            throw new \RuntimeException("{$name}: maksimal dua jadwal NISSO GIKEN per tahun.");
        }

        $proposed = Carbon::parse($data['tanggal'])->startOfDay();
        if ($proposed->year !== (int) $this->tahun) {
            throw new \RuntimeException("{$name}: tahun data harus sama dengan tahun pada tanggal.");
        }
        $dates = array_merge(array_column($existing, 'tanggal'), $planned);
        foreach ($dates as $date) {
            $previous = Carbon::parse($date)->startOfDay();
            $earlier = $proposed->lessThan($previous) ? $proposed : $previous;
            $later = $proposed->greaterThan($previous) ? $proposed : $previous;
            if ($earlier->copy()->addMonthsNoOverflow(6)->greaterThan($later)) {
                throw new \RuntimeException("{$name}: jarak jadwal NISSO GIKEN harus minimal enam bulan.");
            }
        }
        $this->plannedNissoSchedules[$name][] = $data['tanggal'];
    }
}
