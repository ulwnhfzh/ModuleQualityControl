<?php

namespace App\Observers;

use App\Models\ListUpNg;
use App\Models\InternalCalibrationListUp;
use App\Models\ExternalCalibrationListUp;
use App\Models\InternalVerificationSchedule;
use App\Services\EquipmentIdentityMatcher;
use App\Services\ListUpNgProductionSynchronizer;
use App\Services\ListUpNgRecapSynchronizer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ListUpNgObserver
{
    public function created(ListUpNg $ng): void
    {
        $this->syncToOtherTables($ng);
        $this->syncRecapChanges([], [$ng->getAttributes()]);
    }

    public function updated(ListUpNg $ng): void
    {
        $oldAttributes = $ng->getRawOriginal();
        $this->syncToOtherTables($ng);
        $this->syncRecapChanges([$oldAttributes], [$ng->getAttributes()]);
    }

    public function deleted(ListUpNg $ng): void
    {
        $this->syncRecapChanges([$ng->getRawOriginal()], []);
    }

    private function syncRecapChanges(array $oldRecords, array $newRecords): void
    {
        if (ListUpNgRecapSynchronizer::isAutomaticSyncSuspended()) {
            return;
        }

        app(ListUpNgRecapSynchronizer::class)->syncChanges($oldRecords, $newRecords);
    }

    private function syncToOtherTables(ListUpNg $ng): void
    {
        $date = Carbon::parse($ng->ng_date);
        $year = (int) $date->year;
        $toolName = $this->normalizeKey($ng->tool_name);
        $dept = $this->canonicalDivision($ng->dept);
        $category = strtoupper(trim($ng->category_ng));
        $isExternalCalibration = str_contains($category, 'EKSTENAL') || str_contains($category, 'EKSTERNAL');

        app(ListUpNgProductionSynchronizer::class)->sync($ng);

        if (str_contains($category, 'KALIBRASI') && ! $isExternalCalibration) {
            $calibrationRows = InternalCalibrationListUp::where('tahun', $year)
                ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
                ->get()
                ->filter(fn (InternalCalibrationListUp $row): bool =>
                    $this->canonicalDivision($row->shop) === $dept
                    && $this->matchesEquipmentIdentity($ng->getAttributes(), [
                        'type' => $row->tool_type,
                        'size' => $row->specification,
                        'number_seri' => $row->serial_number,
                        'number_reg' => $row->registration_number,
                    ])
                );

            if ($calibrationRows->count() === 1) {
                $calibration = $calibrationRows->first();
                foreach ([
                    ['date' => 'calibration_date', 'status' => 'jgm'],
                    ['date' => 'period2_calibration_date', 'status' => 'period2_jgm'],
                ] as $period) {
                    $calibrationDate = $calibration->getAttribute($period['date']);
                    if ($calibrationDate && Carbon::parse($calibrationDate)->format('Y-m') === $date->format('Y-m')) {
                        $this->markNgUnlessOk($calibration, $period['status'], $ng, 'internal calibration');
                    }
                }
            } elseif ($calibrationRows->count() > 1) {
                $this->logAmbiguousMatch($ng, $calibrationRows->count(), 'internal calibration');
            }
        }

        if (str_contains($category, 'KALIBRASI') && $isExternalCalibration) {
            $calibrationRows = ExternalCalibrationListUp::where('tahun', $year)
                ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
                ->get()
                ->filter(fn (ExternalCalibrationListUp $row): bool =>
                    $this->canonicalDivision($row->shop) === $dept
                    && $this->matchesExternalCalibrationIdentity($ng->getAttributes(), $row)
                );

            if ($calibrationRows->count() === 1) {
                $calibration = $calibrationRows->first();
                $calibrationDate = $calibration->calibration_date;
                if ($calibrationDate && Carbon::parse($calibrationDate)->format('Y-m') === $date->format('Y-m')) {
                    $this->markNgUnlessOk($calibration, 'jgm', $ng, 'external calibration');
                }
            } elseif ($calibrationRows->count() > 1) {
                $this->logAmbiguousMatch($ng, $calibrationRows->count(), 'external calibration');
            }
        }

        if (str_contains($category, 'VERIFIKASI')) {
            $verificationRows = InternalVerificationSchedule::where('tahun', $year)
                ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
                ->get()
                ->filter(fn (InternalVerificationSchedule $row): bool =>
                    $this->canonicalDivision($row->shop) === $dept
                    && $this->matchesEquipmentIdentity($ng->getAttributes(), [
                        'type' => $row->tool_type,
                        'size' => $row->specification,
                        'number_seri' => $row->serial_number,
                        'number_reg' => $row->registration_number,
                    ])
                );

            if ($verificationRows->count() === 1) {
                $verification = $verificationRows->first();
                foreach ([
                    ['date' => 'period1_verification_date', 'status' => 'period1_jgm'],
                    ['date' => 'period2_verification_date', 'status' => 'period2_jgm'],
                ] as $period) {
                    $verificationDate = $verification->getAttribute($period['date']);
                    if ($verificationDate && Carbon::parse($verificationDate)->format('Y-m') === $date->format('Y-m')) {
                        $this->markNgUnlessOk($verification, $period['status'], $ng, 'internal verification');
                    }
                }
            } elseif ($verificationRows->count() > 1) {
                $this->logAmbiguousMatch($ng, $verificationRows->count(), 'internal verification');
            }
        }
    }

    private function canonicalDivision(mixed $value): ?string
    {
        $value = $this->normalizeKey($value);
        if ($value === '') {
            return null;
        }

        return match ($value) {
            'QA1' => 'QA 1',
            'QA2' => 'QA 2',
            'LSCC', 'L.SCC' => 'LINE SCC',
            'QA INCOMING' => 'QA INC',
            'QA INSPECTION' => 'QA INSP',
            default => $value,
        };
    }

    private function matchesEquipmentIdentity(array $source, array $target): bool
    {
        return app(EquipmentIdentityMatcher::class)->matches($source, $target);
    }

    private function matchesExternalCalibrationIdentity(array $source, ExternalCalibrationListUp $target): bool
    {
        if ($this->normalizeKey($source['size'] ?? null) !== $this->normalizeKey($target->specification)) {
            return false;
        }

        $sourceRegistration = $this->normalizeKey($source['number_reg'] ?? null);
        $targetRegistration = $this->normalizeKey($target->registration_number);
        if ($sourceRegistration !== '' && $targetRegistration !== '') {
            return $sourceRegistration === $targetRegistration;
        }

        $sourceSerial = $this->normalizeKey($source['number_seri'] ?? null);
        $targetSerial = $this->normalizeKey($target->serial_number);
        if ($sourceSerial !== '' && $targetSerial !== '') {
            return $sourceSerial === $targetSerial;
        }

        return $sourceRegistration === ''
            && $targetRegistration === ''
            && $sourceSerial === ''
            && $targetSerial === '';
    }

    private function markNgUnlessOk(Model $model, string $field, ListUpNg $ng, string $table): void
    {
        if (strtoupper(trim((string) $model->getAttribute($field))) === 'OK') {
            Log::warning("List Up NG tidak menimpa status OK pada periode yang sama di {$table}.", [
                'list_up_ng_id' => $ng->id,
                'target_id' => $model->getKey(),
                'status_field' => $field,
            ]);

            return;
        }

        if (strtoupper(trim((string) $model->getAttribute($field))) !== 'NG') {
            $model->setAttribute($field, 'NG');
            $model->saveQuietly();
        }
    }

    private function normalizeKey(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return in_array($value, ['-', 'N/A', 'NA'], true) ? '' : $value;
    }

    private function logAmbiguousMatch(ListUpNg $ng, int $matches, string $table): void
    {
        Log::warning("List Up NG tidak disinkronkan ke {$table} karena identitas alat ambigu.", [
            'list_up_ng_id' => $ng->id,
            'matches' => $matches,
        ]);
    }
}