<?php

namespace App\Services;

use App\Models\ExternalCalibrationListUp;
use App\Models\InternalCalibrationListUp;
use App\Models\InternalVerificationSchedule;
use App\Models\ListUpNg;
use App\Models\ProductionData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListUpNgStatusSynchronizer
{
    private const SNAPSHOT_TABLE = 'list_up_ng_status_snapshots';
    private static int $suspensionDepth = 0;

    private const TARGETS = [
        'production' => ProductionData::class,
        'internal_calibration' => InternalCalibrationListUp::class,
        'external_calibration' => ExternalCalibrationListUp::class,
        'internal_verification' => InternalVerificationSchedule::class,
    ];

    public function syncChanges(array $oldRecords, array $newRecords): void
    {
        $targets = [];
        foreach (array_merge($oldRecords, $newRecords) as $attributes) {
            $ng = new ListUpNg;
            $ng->setRawAttributes($attributes, true);
            $this->collectAffectedTargets($ng, $targets);
        }

        $this->reconcileTargets($targets);
    }

    public static function withoutAutomaticSync(callable $callback): mixed
    {
        self::$suspensionDepth++;

        try {
            return $callback();
        } finally {
            self::$suspensionDepth--;
        }
    }

    public static function isAutomaticSyncSuspended(): bool
    {
        return self::$suspensionDepth > 0;
    }

    public function syncTarget(string $type, int $targetId): void
    {
        if (! isset(self::TARGETS[$type])) {
            throw new \InvalidArgumentException("Unknown List Up NG target type: {$type}");
        }

        $targets = [];
        foreach (DB::table(self::SNAPSHOT_TABLE)->where('target_type', $type)->where('target_id', $targetId)->get() as $snapshot) {
            $key = "{$snapshot->target_type}:{$snapshot->target_id}:{$snapshot->status_field}";
            $targets[$key] = [$type, $targetId, $snapshot->status_field];
        }

        $target = self::TARGETS[$type]::query()->find($targetId);
        if ($target === null) {
            DB::table(self::SNAPSHOT_TABLE)->where('target_type', $type)->where('target_id', $targetId)->delete();

            return;
        }

        $year = (int) ($type === 'production' ? $target->year : $target->tahun);
        $toolName = $this->normalizeKey($target->tool_name);
        if ($toolName !== '') {
            $sources = ListUpNg::query()
                ->where('tahun', $year)
                ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
                ->get();
            $candidates = $this->loadCandidates($year, $toolName);
            $productionRows = $candidates['production']->get($toolName, collect());

            foreach ($sources as $ng) {
                if ($this->sourceMatchesTarget($type, $ng, $target, $productionRows)) {
                    $this->collectAffectedTargets($ng, $targets, $candidates);
                }
            }
        }

        $this->reconcileTargets($targets);
    }

    private function reconcileTargets(array $targets): void
    {
        DB::transaction(function () use ($targets): void {
            $productionTargetIds = [];
            foreach ($targets as [$type, $targetId, $field]) {
                $target = self::TARGETS[$type]::query()->find($targetId);
                if ($target === null) {
                    $this->deleteSnapshot($type, $targetId, $field);

                    continue;
                }

                if ($type === 'production') {
                    $productionTargetIds[$targetId] = true;
                    if ($field === 'is_year_template') {
                        continue;
                    }
                }

                $hasActiveNg = $this->hasActiveNg($type, $target, $field);
                $this->reconcileField($type, $target, $field, $hasActiveNg);
            }

            foreach (array_keys($productionTargetIds) as $targetId) {
                $target = ProductionData::query()->find($targetId);
                if ($target === null) {
                    $this->deleteSnapshot('production', $targetId, 'is_year_template');

                    continue;
                }

                $hasActiveNg = $this->hasActiveNg('production', $target, 'is_year_template');
                $this->reconcileField('production', $target, 'is_year_template', $hasActiveNg);
            }
        });
    }

    public function syncYear(int $year): int
    {
        $records = ListUpNg::query()->where('tahun', $year)->get();
        $candidates = $this->loadCandidates($year);

        $targets = [];
        foreach ($records as $ng) {
            $this->collectAffectedTargets($ng, $targets, $candidates);
        }
        foreach (DB::table(self::SNAPSHOT_TABLE)->where('target_year', $year)->get() as $snapshot) {
            $key = "{$snapshot->target_type}:{$snapshot->target_id}:{$snapshot->status_field}";
            $targets[$key] = [$snapshot->target_type, (int) $snapshot->target_id, $snapshot->status_field];
        }
        $this->reconcileTargets($targets);

        $productionIds = ProductionData::query()->where('year', $year)->pluck('id');
        DB::table(self::SNAPSHOT_TABLE)
            ->where('target_type', 'production')
            ->where('target_year', $year)
            ->whereNotIn('target_id', $productionIds)
            ->delete();

        $productionIds = [];
        foreach ($targets as [$type, $id, $field]) {
            if ($type === 'production' && $field !== 'is_year_template') {
                $productionIds[$id] = true;
            }
        }

        return count($productionIds);
    }

    private function loadCandidates(int $year, ?string $toolName = null): array
    {
        $filterByToolName = static function ($query) use ($toolName): void {
            if ($toolName !== null) {
                $query->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName]);
            }
        };
        $productionQuery = ProductionData::query()->where('year', $year);
        $filterByToolName($productionQuery);
        $internalCalibrationQuery = InternalCalibrationListUp::query()->where('tahun', $year);
        $filterByToolName($internalCalibrationQuery);
        $externalCalibrationQuery = ExternalCalibrationListUp::query()->where('tahun', $year);
        $filterByToolName($externalCalibrationQuery);
        $internalVerificationQuery = InternalVerificationSchedule::query()->where('tahun', $year);
        $filterByToolName($internalVerificationQuery);

        $rows = [
            'production' => $productionQuery->get(),
            'internal_calibration' => $internalCalibrationQuery->get(),
            'external_calibration' => $externalCalibrationQuery->get(),
            'internal_verification' => $internalVerificationQuery->get(),
        ];

        foreach ($rows as $type => $candidates) {
            $rows[$type] = $candidates->groupBy(fn (Model $row): string => mb_strtoupper(trim((string) $row->tool_name)));
        }

        return $rows;
    }

    private function collectAffectedTargets(ListUpNg $ng, array &$targets, ?array $candidates = null): void
    {
        $date = Carbon::parse($ng->ng_date);
        $year = (int) $date->year;
        $toolName = $this->normalizeKey($ng->tool_name);
        $department = $this->canonicalDivision($ng->dept);
        if ($toolName === '') {
            return;
        }

        if ($this->normalizeKey($ng->type) !== '' || $this->normalizeKey($ng->size) !== '') {
            $productionRows = $candidates === null
                ? $this->resolveProductionMatches($ng)
                : $this->resolveProductionCandidates($ng, $candidates['production']->get($toolName, collect()));
            if ($productionRows->count() === 1) {
                $this->addTarget($targets, 'production', $productionRows->first()->id, strtolower($date->format('M')));
                $this->addTarget($targets, 'production', $productionRows->first()->id, 'is_year_template');
            } else {
                $this->logUnmatchedProduction($ng, $productionRows->count());
            }
        } else {
            $this->logUnmatchedProduction($ng, 0);
        }

        $category = $this->categoryType($ng->category_ng);
        if ($category === null) {
            return;
        }

        $class = match ($category) {
            'internal_calibration' => InternalCalibrationListUp::class,
            'external_calibration' => ExternalCalibrationListUp::class,
            'internal_verification' => InternalVerificationSchedule::class,
        };
        $rows = ($candidates === null
            ? $class::query()
                ->where('tahun', $year)
                ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
                ->get()
            : $candidates[$category]->get($toolName, collect()))
            ->filter(fn (Model $row): bool => $this->canonicalDivision($row->getAttribute('shop')) === $department
                && $this->matchesTargetIdentity($category, $ng->getAttributes(), $row)
            );

        if ($rows->count() !== 1) {
            if ($rows->count() > 1) {
                $this->logAmbiguousMatch($ng, $rows->count(), $category);
            }

            return;
        }

        $row = $rows->first();
        foreach ($this->periods($category) as $period) {
            $periodDate = $row->getAttribute($period['date']);
            if ($periodDate && Carbon::parse($periodDate)->format('Y-m') === $date->format('Y-m')) {
                $this->addTarget($targets, $category, $row->getKey(), $period['status']);
            }
        }
    }

    private function resolveProductionMatches(ListUpNg $ng)
    {
        $matches = app(EquipmentIdentityMatcher::class)->productionMatches($ng);
        $department = $this->canonicalDivision($ng->dept);
        $departmentMatches = $matches->filter(fn (ProductionData $row): bool => $this->canonicalDivision($row->division) === $department
        );

        return $departmentMatches->isNotEmpty()
            ? $departmentMatches->values()
            : ($matches->count() === 1 ? $matches : collect());
    }

    private function resolveProductionCandidates(ListUpNg $ng, Collection $candidates): Collection
    {
        $matcher = app(EquipmentIdentityMatcher::class);
        $matches = $candidates->filter(fn (ProductionData $row): bool => $matcher->matches($ng->getAttributes(), [
            'type' => $row->type,
            'size' => $row->size,
            'number_seri' => $row->number_seri,
            'number_reg' => $row->number_reg,
        ]));
        $department = $this->canonicalDivision($ng->dept);
        $departmentMatches = $matches->filter(fn (ProductionData $row): bool => $this->canonicalDivision($row->division) === $department);

        return $departmentMatches->isNotEmpty()
            ? $departmentMatches->values()
            : ($matches->count() === 1 ? $matches : collect());
    }

    private function hasActiveNg(string $type, Model $target, string $field): bool
    {
        $year = (int) ($type === 'production' ? $target->year : $target->tahun);
        $toolName = $this->normalizeKey($target->tool_name);
        if ($toolName === '') {
            return false;
        }

        $query = ListUpNg::query()
            ->where('tahun', $year)
            ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName]);

        if ($type === 'production') {
            if ($field !== 'is_year_template') {
                $monthNumber = array_search($field, ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'], true);
                if ($monthNumber === false) {
                    return false;
                }
                $period = Carbon::create($year, $monthNumber + 1, 1);
                $query->whereBetween('ng_date', [$period->copy()->startOfMonth()->toDateString(), $period->copy()->endOfMonth()->toDateString()]);
            }
        } else {
            $periodDate = null;
            foreach ($this->periods($type) as $period) {
                if ($period['status'] === $field) {
                    $periodDate = $target->getAttribute($period['date']);
                    break;
                }
            }
            if ($periodDate === null) {
                return false;
            }
            $period = Carbon::parse($periodDate);
            $query->whereBetween('ng_date', [$period->copy()->startOfMonth()->toDateString(), $period->copy()->endOfMonth()->toDateString()]);
        }

        $sources = $query->get();
        if ($sources->isEmpty()) {
            return false;
        }

        if ($type === 'production') {
            $productionRows = ProductionData::query()
                ->where('year', $year)
                ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
                ->get();
            $matcher = app(EquipmentIdentityMatcher::class);

            foreach ($sources as $ng) {
                if ($this->normalizeKey($ng->type) === '' && $this->normalizeKey($ng->size) === '') {
                    continue;
                }

                $matches = $productionRows->filter(fn (ProductionData $row): bool => $matcher->matches($ng->getAttributes(), [
                    'type' => $row->type,
                    'size' => $row->size,
                    'number_seri' => $row->number_seri,
                    'number_reg' => $row->number_reg,
                ]));
                $departmentMatches = $matches->filter(fn (ProductionData $row): bool =>
                    $this->canonicalDivision($row->division) === $this->canonicalDivision($ng->dept)
                );
                $selectedMatches = $departmentMatches->isNotEmpty()
                    ? $departmentMatches
                    : ($matches->count() === 1 ? $matches : collect());

                if ($selectedMatches->contains('id', $target->getKey())) {
                    return true;
                }
            }

            return false;
        }

        foreach ($sources as $ng) {
            $date = Carbon::parse($ng->ng_date);
            if (! $this->matchesTargetPeriod($type, $target, $field, $date)) {
                continue;
            }

            if ($this->categoryType($ng->category_ng) !== $type) {
                continue;
            }

            if ($this->canonicalDivision($ng->dept) !== $this->canonicalDivision($target->shop)) {
                continue;
            }

            if ($this->matchesTargetIdentity($type, $ng->getAttributes(), $target)) {
                return true;
            }
        }

        return false;
    }

    private function sourceMatchesTarget(string $type, ListUpNg $ng, Model $target, Collection $productionRows): bool
    {
        $date = Carbon::parse($ng->ng_date);
        if ($type === 'production') {
            if ($this->normalizeKey($ng->type) === '' && $this->normalizeKey($ng->size) === '') {
                return false;
            }

            if ((int) $target->year !== (int) $date->year) {
                return false;
            }

            $matcher = app(EquipmentIdentityMatcher::class);
            $matches = $productionRows->filter(fn (ProductionData $row): bool => $matcher->matches($ng->getAttributes(), [
                'type' => $row->type,
                'size' => $row->size,
                'number_seri' => $row->number_seri,
                'number_reg' => $row->number_reg,
            ]));
            $departmentMatches = $matches->filter(fn (ProductionData $row): bool =>
                $this->canonicalDivision($row->division) === $this->canonicalDivision($ng->dept)
            );
            $selectedMatches = $departmentMatches->isNotEmpty()
                ? $departmentMatches
                : ($matches->count() === 1 ? $matches : collect());

            return $selectedMatches->contains('id', $target->getKey());
        }

        if ($this->categoryType($ng->category_ng) !== $type
            || $this->canonicalDivision($ng->dept) !== $this->canonicalDivision($target->shop)
            || ! $this->matchesTargetIdentity($type, $ng->getAttributes(), $target)) {
            return false;
        }

        foreach ($this->periods($type) as $period) {
            $periodDate = $target->getAttribute($period['date']);
            if ($periodDate && Carbon::parse($periodDate)->format('Y-m') === $date->format('Y-m')) {
                return true;
            }
        }

        return false;
    }

    private function matchesTargetPeriod(string $type, Model $target, string $field, Carbon $ngDate): bool
    {
        $targetYear = (int) ($type === 'production' ? $target->year : $target->tahun);
        if ($targetYear !== (int) $ngDate->year) {
            return false;
        }

        if ($type === 'production') {
            if ($field === 'is_year_template') {
                return true;
            }

            return strtolower($ngDate->format('M')) === $field;
        }

        foreach ($this->periods($type) as $period) {
            if ($period['status'] !== $field) {
                continue;
            }
            $date = $target->getAttribute($period['date']);

            return $date !== null && Carbon::parse($date)->format('Y-m') === $ngDate->format('Y-m');
        }

        return false;
    }

    private function matchesTargetIdentity(string $type, array $source, Model $target): bool
    {
        if ($type === 'external_calibration') {
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

        $targetIdentity = [
            'type' => $target->getAttribute('tool_type'),
            'size' => $target->getAttribute('specification'),
            'number_seri' => $target->getAttribute('serial_number'),
            'number_reg' => $target->getAttribute('registration_number'),
        ];

        return app(EquipmentIdentityMatcher::class)->matches($source, $targetIdentity);
    }

    private function reconcileField(string $type, Model $target, string $field, bool $hasActiveNg): void
    {
        $snapshotQuery = DB::table(self::SNAPSHOT_TABLE)
            ->where('target_type', $type)
            ->where('target_id', $target->getKey())
            ->where('status_field', $field)
            ->lockForUpdate();
        $snapshot = $snapshotQuery->first();
        $current = $target->getAttribute($field);
        $isTemplate = $field === 'is_year_template';

        if ($hasActiveNg) {
            if ($type !== 'production' && strtoupper(trim((string) $current)) === 'OK') {
                if ($snapshot !== null) {
                    $this->deleteSnapshot($type, $target->getKey(), $field);
                }

                return;
            }

            if ($snapshot === null) {
                DB::table(self::SNAPSHOT_TABLE)->insertOrIgnore([
                    'target_type' => $type,
                    'target_year' => (int) ($type === 'production' ? $target->year : $target->tahun),
                    'target_id' => $target->getKey(),
                    'status_field' => $field,
                    'original_value' => $current === null ? null : (string) $current,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $desired = $isTemplate ? false : 'NG';
            $alreadyNg = $isTemplate
                ? $current === false
                : ($type === 'production'
                    ? $target->resultStatus($field) === 'NG'
                    : strtoupper(trim((string) $current)) === 'NG');

            if (! $alreadyNg) {
                $target->setAttribute($field, $desired);
                $target->saveQuietly();
            }

            return;
        }

        if ($snapshot === null) {
            return;
        }

        $shouldRestore = $isTemplate
            ? $current === false
            : ($type === 'production'
                ? $target->resultStatus($field) === 'NG'
                : strtoupper(trim((string) $current)) === 'NG');

        if ($shouldRestore) {
            $target->setAttribute($field, $snapshot->original_value);
            $target->saveQuietly();
        }

        $this->deleteSnapshot($type, $target->getKey(), $field);
    }

    private function periods(string $type): array
    {
        return match ($type) {
            'internal_calibration' => [
                ['date' => 'calibration_date', 'status' => 'jgm'],
                ['date' => 'period2_calibration_date', 'status' => 'period2_jgm'],
            ],
            'external_calibration' => [
                ['date' => 'calibration_date', 'status' => 'jgm'],
            ],
            'internal_verification' => [
                ['date' => 'period1_verification_date', 'status' => 'period1_jgm'],
                ['date' => 'period2_verification_date', 'status' => 'period2_jgm'],
            ],
            default => [],
        };
    }

    private function categoryType(mixed $category): ?string
    {
        $category = strtoupper(trim((string) $category));
        $isCalibration = str_contains($category, 'KALIBRASI');
        $isExternal = str_contains($category, 'EKSTENAL') || str_contains($category, 'EKSTERNAL');

        if ($isCalibration) {
            return $isExternal ? 'external_calibration' : 'internal_calibration';
        }
        if (str_contains($category, 'VERIFIKASI')) {
            return 'internal_verification';
        }

        return null;
    }

    private function addTarget(array &$targets, string $type, int $id, string $field): void
    {
        $targets["{$type}:{$id}:{$field}"] = [$type, $id, $field];
    }

    private function deleteSnapshot(string $type, int $targetId, string $field): void
    {
        DB::table(self::SNAPSHOT_TABLE)
            ->where('target_type', $type)
            ->where('target_id', $targetId)
            ->where('status_field', $field)
            ->delete();
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
            'P1' => 'PRODUKSI 1',
            'P2' => 'PRODUKSI 2',
            'P3' => 'PRODUKSI 3',
            'P5' => 'PRODUKSI 5',
            'P6' => 'PRODUKSI 6',
            'P7' => 'PRODUKSI 7',
            'P8' => 'PRODUKSI 8',
            'ENG' => 'ENGINEERING',
            default => $value,
        };
    }

    private function normalizeKey(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return in_array($value, ['-', 'N/A', 'NA'], true) ? '' : $value;
    }

    private function logAmbiguousMatch(ListUpNg $ng, int $matches, string $target): void
    {
        Log::warning("List Up NG tidak disinkronkan ke {$target} karena identitas alat ambigu.", [
            'list_up_ng_id' => $ng->id,
            'matches' => $matches,
        ]);
    }

    private function logUnmatchedProduction(ListUpNg $ng, int $matches): void
    {
        Log::warning('List Up NG tidak disinkronkan ke Data All Produksi karena identitas alat tidak cocok atau ambigu.', [
            'list_up_ng_id' => $ng->id,
            'matches' => $matches,
        ]);
    }
}
