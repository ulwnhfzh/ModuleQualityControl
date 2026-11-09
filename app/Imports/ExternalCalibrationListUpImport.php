<?php

namespace App\Imports;

use App\Models\ExternalCalibrationListUp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ExternalCalibrationListUpImport implements ToCollection, WithStartRow
{
    public function __construct(private readonly int $year, private readonly int $headerRow = 1) {}

    public function startRow(): int { return $this->headerRow + 1; }

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $number = trim((string) ($row[0] ?? ''));
                if ($number === '' || !is_numeric($number)) continue; // Ignore title and second header rows.

                $name = $this->text($row[1] ?? null);
                if ($name === null) continue;

                $serial = $this->text($row[3] ?? null);
                $registration = $this->text($row[4] ?? null);
                $identity = [
                    'tahun' => $this->year,
                    'tool_name' => mb_strtoupper($name),
                    'serial_number' => $serial,
                    'registration_number' => $registration,
                ];

                ExternalCalibrationListUp::updateOrCreate($identity, [
                    'specification' => $this->text($row[2] ?? null),
                    'shop' => $this->text($row[5] ?? null),
                    'calibration_date' => SpreadsheetInput::date($row[6] ?? null, "{$name} / calibration date"),
                    'next_calibration_date' => SpreadsheetInput::date($row[7] ?? null, "{$name} / next calibration date"),
                    'jgm' => $this->text($row[8] ?? null),
                    'calibration_count' => $this->integerOrNull($row[9] ?? null, $name),
                    'note' => $this->text($row[10] ?? null),
                ]);
            }
        });
    }

    private function text(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' || $value === '-' ? null : $value;
    }

    private function integerOrNull(mixed $value, string $name): ?int
    {
        if ($value === null || trim((string) $value) === '') return null;
        return SpreadsheetInput::integer($value, "{$name} / number calibration");
    }
}
