<?php

namespace App\Imports;

use App\Models\VerifikasiInternalSchedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VerifikasiPeriodeImport implements ToCollection, WithHeadingRow
{
    public function __construct(private readonly int $tahun, private readonly int $rowHeader = 1) {}

    public function headingRow(): int { return $this->rowHeader; }

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $data = $row->toArray();
                $name = trim((string) ($data['tool_name'] ?? $data['nama_alat'] ?? ''));
                if ($name === '' || in_array(mb_strtoupper($name), ['APPROVED', 'CHECKED', 'ISSUED'], true)) {
                    continue;
                }
                $type = $this->nullableString($data['tool_type'] ?? $data['type'] ?? null);
                if ($type === null) {
                    throw new \RuntimeException('Setiap baris List Up harus memiliki Type.');
                }

                $serial = $this->nullableString($data['serial_number'] ?? $data['number_seri'] ?? null);
                $identity = ['tahun' => $this->tahun, 'tool_name' => $name];
                if ($serial !== null) {
                    $identity['serial_number'] = $serial;
                }

                VerifikasiInternalSchedule::updateOrCreate($identity, [
                'tool_type' => $type,
                'specification' => $this->nullableString($data['specification'] ?? null),
                'serial_number' => $serial,
                'registration_number' => $this->nullableString($data['registration_number'] ?? $data['number_registration'] ?? null),
                'shop' => $this->nullableString($data['shop'] ?? null),
                'period1_verification_date' => SpreadsheetInput::date($data['period1_verification_date'] ?? $data['period_1_verification_date'] ?? $data['periode_1_verification_date'] ?? null, "{$name} / period1_verification_date"),
                'period1_next_verification_date' => SpreadsheetInput::date($data['period1_next_verification_date'] ?? $data['period_1_next_verification_date'] ?? $data['periode_1_next_verification_date'] ?? null, "{$name} / period1_next_verification_date"),
                'period1_jgm' => $this->nullableString($data['period1_jgm'] ?? $data['period_1_jgm'] ?? $data['periode_1_jgm'] ?? null),
                'period2_verification_date' => SpreadsheetInput::date($data['period2_verification_date'] ?? $data['period_2_verification_date'] ?? $data['periode_2_verification_date'] ?? null, "{$name} / period2_verification_date"),
                'period2_next_verification_date' => SpreadsheetInput::date($data['period2_next_verification_date'] ?? $data['period_2_next_verification_date'] ?? $data['periode_2_next_verification_date'] ?? null, "{$name} / period2_next_verification_date"),
                'period2_jgm' => $this->nullableString($data['period2_jgm'] ?? $data['period_2_jgm'] ?? $data['periode_2_jgm'] ?? null),
                'tool_id' => null,
                ]);
            }
        });
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }
}
