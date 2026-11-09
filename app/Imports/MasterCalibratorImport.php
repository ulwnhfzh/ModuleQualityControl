<?php

namespace App\Imports;

use App\Models\MasterCalibrator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class MasterCalibratorImport implements ToCollection, WithStartRow
{
    public function __construct(private int $year) {}

    public function startRow(): int { return 4; }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = trim((string) ($row[1] ?? ''));
            if ($name === '') continue;

            $data = [
                'tahun' => $this->year,
                'tool_name' => strtoupper($name),
                'type' => $this->value($row[2] ?? null),
                'size' => $this->value($row[3] ?? null),
                'number_seri' => $this->value($row[4] ?? null),
                'number_reg' => $this->value($row[5] ?? null),
                'part_name' => $this->value($row[6] ?? null),
                'part_number' => $this->value($row[7] ?? null),
                'note' => $this->value($row[21] ?? null),
            ];
            foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $index => $month) {
                $data[$month] = $this->value($row[8 + $index] ?? null);
            }

            MasterCalibrator::updateOrCreate(
                ['tahun' => $data['tahun'], 'tool_name' => $data['tool_name'], 'size' => $data['size'], 'number_reg' => $data['number_reg']],
                $data
            );
        }
    }

    private function value(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' || $value === '-' ? null : $value;
    }
}
