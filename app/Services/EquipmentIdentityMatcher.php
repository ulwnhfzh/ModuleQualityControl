<?php

namespace App\Services;

use App\Models\ListUpNg;
use App\Models\ProductionData;
use Illuminate\Support\Collection;

class EquipmentIdentityMatcher
{
    public function productionMatches(ListUpNg $ng): Collection
    {
        $toolName = $this->normalizeKey($ng->tool_name);
        if ($toolName === '') {
            return collect();
        }

        $year = (int) ($ng->tahun ?: \Carbon\Carbon::parse($ng->ng_date)->year);

        return ProductionData::query()
            ->where('year', $year)
            ->whereRaw('UPPER(TRIM(tool_name)) = ?', [$toolName])
            ->get()
            ->filter(fn (ProductionData $row): bool => $this->matches($ng->getAttributes(), [
                'type' => $row->type,
                'size' => $row->size,
                'number_seri' => $row->number_seri,
                'number_reg' => $row->number_reg,
            ]))
            ->values();
    }

    public function matches(array $source, array $target): bool
    {
        foreach (['type', 'size'] as $field) {
            if ($this->normalizeKey($source[$field] ?? null) !== $this->normalizeKey($target[$field] ?? null)) {
                return false;
            }
        }

        $sourceRegistration = $this->normalizeKey($source['number_reg'] ?? null);
        $targetRegistration = $this->normalizeKey($target['number_reg'] ?? null);
        if ($sourceRegistration !== '' && $targetRegistration !== '') {
            return $sourceRegistration === $targetRegistration;
        }

        $sourceSerial = $this->normalizeKey($source['number_seri'] ?? null);
        $targetSerial = $this->normalizeKey($target['number_seri'] ?? null);
        if ($sourceSerial !== '' && $targetSerial !== '') {
            return $sourceSerial === $targetSerial;
        }

        return $sourceRegistration === ''
            && $targetRegistration === ''
            && $sourceSerial === ''
            && $targetSerial === '';
    }

    private function normalizeKey(mixed $value): string
    {
        $value = mb_strtoupper(trim((string) ($value ?? '')));
        $value = preg_replace('/\s+/', ' ', $value) ?? '';

        return in_array($value, ['-', 'N/A', 'NA'], true) ? '' : $value;
    }
}
