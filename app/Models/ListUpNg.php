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
        'part_name', 'part_number', 'ng_date', 'dept', 'category_ng', 'note',
        'stock_qa', 'stock_prod', 'no_spp', 'status_order', 'note_order'
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