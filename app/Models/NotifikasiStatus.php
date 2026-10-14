<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifikasiStatus extends Model
{
    use HasFactory;

    protected $table = 'notifikasi_status';

    protected $fillable = [
        'alat_id',
        'alat_nama',
        'next_date',
        'status',
        'catatan',
        'user_id',
        'user_name',
        'diproses_at',
        'selesai_at',
    ];

    protected $casts = [
        'next_date' => 'date',
        'diproses_at' => 'datetime',
        'selesai_at' => 'datetime',
    ];

    /**
     * Relasi ke EksternalMesin
     */
    public function alat()
    {
        return $this->belongsTo(EksternalMesin::class, 'alat_id');
    }

    /**
     * Label status untuk tampilan
     */
    public function getLabelStatusAttribute(): string
    {
        $labels = [
            'diproses' => 'Sedang Disiapkan',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Warna badge status
     */
    public function getWarnaStatusAttribute(): string
    {
        $colors = [
            'diproses' => 'bg-yellow-100 text-yellow-700',
            'selesai' => 'bg-green-100 text-green-700',
            'dibatalkan' => 'bg-gray-100 text-gray-700',
        ];
        return $colors[$this->status] ?? 'bg-gray-100 text-gray-700';
    }
}