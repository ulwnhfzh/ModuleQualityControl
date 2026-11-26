<?php

namespace App\Services;

use App\Models\ExternalCalibrationListUp;
use App\Models\InternalCalibrationListUp;
use App\Models\InternalVerificationSchedule;
use App\Models\Qc2Verification;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ScheduleExpiryNotifier
{
    public const DEFAULT_WARNING_DAYS = 5;

        /** Tanggal sebelum tahun ini dianggap hasil rumus Excel kosong (1899/1900), bukan jadwal asli. */
    public const MIN_VALID_YEAR = 2000;

    public static function warningDays(): int
    {
        $days = (int) Setting::get('warning_days_jadwal', self::DEFAULT_WARNING_DAYS);

        return $days > 0 ? $days : self::DEFAULT_WARNING_DAYS;
    }

    /**
     * Semua jadwal yang sudah masuk jendela peringatan atau sudah lewat jatuh temponya.
     * Setiap item membawa actual_date: tanggal verifikasi/kalibrasi periode yang sudah diisi (atau null).
     */
    public function dueItems(?Carbon $today = null): Collection
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $limit = $today->copy()->addDays(self::warningDays())->endOfDay();
        $items = collect();

        // 1. VERIFIKASI INTERNAL (Periode 1 & Periode 2)
        if (Schema::hasTable('verifikasi_internal_schedules')) {
            InternalVerificationSchedule::query()
                ->where(function ($q) use ($limit) {
                    $q->whereNotNull('period1_next_verification_date')
                        ->whereDate('period1_next_verification_date', '<=', $limit->toDateString())
                        ->orWhere(function ($q2) use ($limit) {
                            $q2->whereNotNull('period2_next_verification_date')
                                ->whereDate('period2_next_verification_date', '<=', $limit->toDateString());
                        });
                })
                ->get()
                ->each(function (InternalVerificationSchedule $row) use ($today, $limit, &$items) {
                    if ($row->period1_next_verification_date && ! $row->period2_verification_date) {
                        $nextDate = Carbon::parse($row->period1_next_verification_date)->startOfDay();
                        if ($nextDate->lte($limit)) {
                            $items->push($this->formatItem(
                                key: "verif_int:{$row->id}:p1",
                                category: 'Verifikasi Internal',
                                company: $row->company,
                                toolName: $row->tool_name,
                                toolType: $row->tool_type,
                                specification: $row->specification,
                                serialNumber: $row->serial_number,
                                registrationNumber: $row->registration_number,
                                shop: $row->shop,
                                nextDate: $nextDate,
                                actualDate: $this->startOfDayOrNull($row->period1_verification_date),
                                today: $today,
                                routeUrl: route('verifikasi.periode.index', [
                                    'tahun' => $row->tahun,
                                    'jenis' => 'verifikasi',
                                    'company' => $row->company,
                                    'shop' => $row->shop,
                                    'tool_name' => $row->tool_name,
                                ]) . '#schedule-' . $row->id,
                            ));
                        }
                    }

                    if ($row->period2_next_verification_date) {
                        $nextDate = Carbon::parse($row->period2_next_verification_date)->startOfDay();
                        if ($nextDate->lte($limit)) {
                            $items->push($this->formatItem(
                                key: "verif_int:{$row->id}:p2",
                                category: 'Verifikasi Internal (Periode 2)',
                                company: $row->company,
                                toolName: $row->tool_name,
                                toolType: $row->tool_type,
                                specification: $row->specification,
                                serialNumber: $row->serial_number,
                                registrationNumber: $row->registration_number,
                                shop: $row->shop,
                                nextDate: $nextDate,
                                actualDate: $this->startOfDayOrNull($row->period2_verification_date),
                                today: $today,
                                routeUrl: route('verifikasi.periode.index', [
                                    'tahun' => $row->tahun,
                                    'jenis' => 'verifikasi',
                                    'company' => $row->company,
                                    'shop' => $row->shop,
                                    'tool_name' => $row->tool_name,
                                ]) . '#schedule-' . $row->id,
                            ));
                        }
                    }
                });
        }

        // 2. VERIFIKASI QC2
        if (Schema::hasTable('verifikasi_qc2')) {
            $qc2DateCol = Schema::hasColumn('verifikasi_qc2', 'next_verification_date')
                ? 'next_verification_date'
                : (Schema::hasColumn('verifikasi_qc2', 'period1_next_verification_date') ? 'period1_next_verification_date' : null);

            if ($qc2DateCol !== null) {
                Qc2Verification::query()
                    ->whereNotNull($qc2DateCol)
                    ->whereDate($qc2DateCol, '<=', $limit->toDateString())
                    ->get()
                    ->each(function (Qc2Verification $row) use ($qc2DateCol, $today, &$items) {
                        $nextDate = Carbon::parse($row->getAttribute($qc2DateCol))->startOfDay();
                        $items->push($this->formatItem(
                            key: "verif_qc2:{$row->id}",
                            category: 'Verifikasi QC2',
                            company: $row->company,
                            toolName: $row->tool_name,
                            toolType: $row->tool_type,
                            specification: $row->specification,
                            serialNumber: $row->serial_number,
                            registrationNumber: $row->registration_number,
                            shop: $row->shop,
                            nextDate: $nextDate,
                            actualDate: $this->startOfDayOrNull($row->verification_date),
                            today: $today,
                            routeUrl: route('verifikasi.periode.index', [
                                'tahun' => $row->tahun,
                                'jenis' => 'verifikasi_qc2',
                                'company' => $row->company,
                                'shop' => $row->shop,
                                'tool_name' => $row->tool_name,
                            ]) . '#schedule-' . $row->id,
                        ));
                    });
            }
        }

        // 3. KALIBRASI INTERNAL
        if (Schema::hasTable('kalibrasi_internal_list_ups')) {
            InternalCalibrationListUp::query()
                ->where(function ($q) use ($limit) {
                    $q->whereNotNull('next_calibration_date')
                        ->whereDate('next_calibration_date', '<=', $limit->toDateString())
                        ->orWhere(function ($q2) use ($limit) {
                            $q2->whereNotNull('period2_next_calibration_date')
                                ->whereDate('period2_next_calibration_date', '<=', $limit->toDateString());
                        });
                })
                ->get()
                ->each(function (InternalCalibrationListUp $row) use ($today, $limit, &$items) {
                    if ($row->next_calibration_date && ! $row->period2_calibration_date) {
                        $nextDate = Carbon::parse($row->next_calibration_date)->startOfDay();
                        if ($nextDate->lte($limit)) {
                            $items->push($this->formatItem(
                                key: "kal_int:{$row->id}:p1",
                                category: 'Kalibrasi Internal',
                                company: $row->company,
                                toolName: $row->tool_name,
                                toolType: $row->tool_type,
                                specification: $row->specification,
                                serialNumber: $row->serial_number,
                                registrationNumber: $row->registration_number,
                                shop: $row->shop,
                                nextDate: $nextDate,
                                actualDate: $this->startOfDayOrNull($row->calibration_date),
                                today: $today,
                                routeUrl: route('verifikasi.periode.index', [
                                    'tahun' => $row->tahun,
                                    'jenis' => 'kalibrasi',
                                    'company' => $row->company,
                                    'shop' => $row->shop,
                                    'tool_name' => $row->tool_name,
                                ]) . '#schedule-' . $row->id,
                            ));
                        }
                    }

                    if ($row->period2_next_calibration_date) {
                        $nextDate = Carbon::parse($row->period2_next_calibration_date)->startOfDay();
                        if ($nextDate->lte($limit)) {
                            $items->push($this->formatItem(
                                key: "kal_int:{$row->id}:p2",
                                category: 'Kalibrasi Internal (Periode 2)',
                                company: $row->company,
                                toolName: $row->tool_name,
                                toolType: $row->tool_type,
                                specification: $row->specification,
                                serialNumber: $row->serial_number,
                                registrationNumber: $row->registration_number,
                                shop: $row->shop,
                                nextDate: $nextDate,
                                actualDate: $this->startOfDayOrNull($row->period2_calibration_date),
                                today: $today,
                                routeUrl: route('verifikasi.periode.index', [
                                    'tahun' => $row->tahun,
                                    'jenis' => 'kalibrasi',
                                    'company' => $row->company,
                                    'shop' => $row->shop,
                                    'tool_name' => $row->tool_name,
                                ]) . '#schedule-' . $row->id,
                            ));
                        }
                    }
                });
        }

        // 4. KALIBRASI EKSTERNAL
        if (Schema::hasTable('external_calibration_list_ups')) {
            ExternalCalibrationListUp::query()
                ->whereNotNull('next_calibration_date')
                ->whereDate('next_calibration_date', '<=', $limit->toDateString())
                ->get()
                ->each(function (ExternalCalibrationListUp $row) use ($today, &$items) {
                    $nextDate = Carbon::parse($row->next_calibration_date)->startOfDay();
                    $items->push($this->formatItem(
                        key: "kal_eks:{$row->id}",
                        category: 'Kalibrasi Eksternal',
                        company: null,
                        toolName: $row->tool_name,
                        toolType: null,
                        specification: $row->specification,
                        serialNumber: $row->serial_number,
                        registrationNumber: $row->registration_number,
                        shop: $row->shop,
                        nextDate: $nextDate,
                        actualDate: $this->startOfDayOrNull($row->calibration_date),
                        today: $today,
                        routeUrl: route('external-list-up.index', [
                            'tahun' => $row->tahun,
                            'shop' => $row->shop,
                            'tool_name' => $row->tool_name,
                        ]) . '#schedule-' . $row->id,
                    ));
                });
        }

        return $items
            ->filter(fn (object $item) => (int) substr($item->next_date, 0, 4) >= self::MIN_VALID_YEAR)
            ->sortBy('next_date')
            ->values();
    }

    private function startOfDayOrNull($value): ?Carbon
    {
        return $value ? Carbon::parse($value)->startOfDay() : null;
    }

    private function formatItem(
        string $key,
        string $category,
        ?string $company,
        string $toolName,
        ?string $toolType,
        ?string $specification,
        ?string $serialNumber,
        ?string $registrationNumber,
        ?string $shop,
        Carbon $nextDate,
        ?Carbon $actualDate,
        Carbon $today,
        string $routeUrl,
    ): object {
        $sisaHari = (int) $today->diffInDays($nextDate, false);

        return (object) [
            'unique_key' => $key,
            'category' => $category,
            'company' => $company,
            'tool_name' => $toolName,
            'tool_type' => $toolType,
            'specification' => $specification,
            'serial_number' => $serialNumber,
            'registration_number' => $registrationNumber,
            'shop' => $shop,
            'next_date' => $nextDate->toDateString(),
            'sisa_hari' => $sisaHari,
            'level' => $sisaHari < 0 ? 'terlambat' : 'mendekati',
            'actual_date' => $actualDate,
            'route_url' => $routeUrl,
        ];
    }
}