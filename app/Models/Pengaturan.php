<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = [
        'key',
        'value',
        'keterangan',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan key
     */
    public static function get(string $key, $default = null)
    {
        $pengaturan = self::where('key', $key)->first();
        return $pengaturan ? $pengaturan->value : $default;
    }

    /**
     * Set nilai pengaturan
     */
    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Ambil warning days cepat (default 7)
     */
    public static function warningCepat(): int
    {
        return (int) self::get('warning_days_cepat', 7);
    }

    /**
     * Ambil warning days standar (default 30)
     */
    public static function warningStandar(): int
    {
        return (int) self::get('warning_days_standar', 30);
    }
}