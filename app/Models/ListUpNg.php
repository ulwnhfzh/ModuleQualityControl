<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Observers\ListUpNgObserver;

class ListUpNg extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun', 'tool_name', 'type', 'size', 'number_seri', 'number_reg',
        'part_name', 'part_number', 'ng_date', 'division', 'category_ng', 'note',
    ];

    protected $casts = [
        'ng_date' => 'date',
    ];

    /**
     * Daftarkan Observer agar sinkronisasi berjalan otomatis
     * saat data List Up NG disimpan (create/update).
     */
    protected static function booted(): void
    {
        static::observe(ListUpNgObserver::class);
    }
}