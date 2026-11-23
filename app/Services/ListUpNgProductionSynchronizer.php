<?php

namespace App\Services;

use App\Models\ListUpNg;
use App\Models\ProductionData;
use Illuminate\Support\Facades\Log;

class ListUpNgProductionSynchronizer
{
    public function sync(ListUpNg $ng): bool
    {
        if ($this->normalizeKey($ng->type) === '' && $this->normalizeKey($ng->size) === '') {
            Log::warning('List Up NG tidak disinkronkan ke Data All Produksi karena type dan size kosong.', [
                'list_up_ng_id' => $ng->id,
            ]);

            return false;
        }

        $matches = app(EquipmentIdentityMatcher::class)->productionMatches($ng);
        $department = $this->canonicalDivision($ng->division);
        $departmentMatches = $matches->filter(fn (ProductionData $row): bool =>
            $this->canonicalDivision($row->division) === $department
        );
        $selectedMatches = $departmentMatches->isNotEmpty()
            ? $departmentMatches
            : ($matches->count() === 1 ? $matches : collect());

        if ($selectedMatches->count() !== 1) {
            Log::warning('List Up NG tidak disinkronkan ke Data All Produksi karena identitas alat tidak cocok atau ambigu.', [
                'list_up_ng_id' => $ng->id,
                'matches' => $selectedMatches->count(),
            ]);

            return false;
        }

        app(ListUpNgStatusSynchronizer::class)->syncChanges([], [$ng->getAttributes()]);

        return true;
    }

    public function syncYear(int $year): int
    {
        return app(ListUpNgStatusSynchronizer::class)->syncYear($year);
    }

    private function canonicalDivision(mixed $value): ?string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
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

    private function normalizeKey(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return in_array($value, ['-', 'N/A', 'NA'], true) ? '' : $value;
    }
}
