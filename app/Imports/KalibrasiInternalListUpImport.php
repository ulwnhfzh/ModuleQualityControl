<?php

namespace App\Imports;

use App\Models\KalibrasiInternalListUp;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class KalibrasiInternalListUpImport implements ToCollection, WithHeadingRow
{
    public function __construct(private readonly int $tahun, private readonly int $rowHeader = 1) {}

    public function headingRow(): int { return $this->rowHeader; }

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $data = $row->toArray();
                $name = $this->value($data, ['tool_name', 'nama_alat', 'nama_alat_ukur']);
                if ($name === null || in_array(mb_strtoupper($name), ['APPROVED', 'CHECKED', 'ISSUED', 'TOTAL'], true)) continue;

                $type = $this->value($data, ['tool_type', 'type', 'tipe']);
                if ($type === null) throw new \RuntimeException("Tipe alat wajib diisi pada baris {$name}.");

                $serial = $this->value($data, ['serial_number', 'number_seri', 'nomor_seri']);
                $identity = ['tahun' => $this->tahun, 'tool_name' => $name, 'serial_number' => $serial];
                KalibrasiInternalListUp::updateOrCreate($identity, [
                    'tool_type' => mb_strtoupper($type),
                    'specification' => $this->value($data, ['specification', 'spesifikasi']),
                    'registration_number' => $this->value($data, ['registration_number', 'number_registration', 'nomor_registrasi']),
                    'shop' => $this->value($data, ['shop', 'bagian']),
                    'calibration_date' => $this->dateValue($this->value($data, ['calibration_date', 'tanggal_kalibrasi'])),
                    'next_calibration_date' => $this->dateValue($this->value($data, ['next_calibration_date', 'next_calibration', 'tanggal_kalibrasi_berikutnya'])),
                    'jgm' => $this->value($data, ['jgm']),
                ]);
            }
        });
    }

    private function value(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') return $value;
        }
        return null;
    }

    private function dateValue(?string $value): ?string
    {
        if ($value === null) return null;
        if (is_numeric($value)) return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        try { return Carbon::parse($value)->format('Y-m-d'); } catch (\Throwable) { return null; }
    }
}
