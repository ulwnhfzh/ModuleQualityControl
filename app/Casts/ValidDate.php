<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Seperti cast 'date', tetapi tanggal sebelum tahun 2000 (hasil rumus Excel kosong,
 * misalnya 1900-06-29 / 1900-12-30) dibaca sebagai NULL supaya tampil "-" / kosong.
 * Nilai di database tidak diubah.
 */
class ValidDate implements CastsAttributes
{
    public const MIN_YEAR = 2000;

    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = Carbon::parse($value)->startOfDay();

        return $date->year < self::MIN_YEAR ? null : $date;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }
}