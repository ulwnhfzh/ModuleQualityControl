<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'aksi',
        'jenis_data',
        'data_id',
        'keterangan',
        'data_lama',
        'data_baru',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by aksi
     */
    public function scopeAksi($query, $aksi)
    {
        return $query->where('aksi', $aksi);
    }

    /**
     * Scope: Filter by jenis data
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis_data', $jenis);
    }

    /**
     * Scope: Filter by tanggal
     */
    public function scopeTanggal($query, $tanggal)
    {
        return $query->whereDate('created_at', $tanggal);
    }

    /**
     * Label aksi (untuk tampilan)
     */
    public function getLabelAksiAttribute(): string
    {
        $labels = [
            'CREATE' => 'Tambah',
            'UPDATE' => 'Edit',
            'DELETE' => 'Hapus',
            'LOGIN'  => 'Login',
            'LOGOUT' => 'Logout',
            'EXPORT' => 'Export',
        ];

        return $labels[$this->aksi] ?? $this->aksi;
    }

    /**
     * Label jenis data (untuk tampilan)
     */
    public function getLabelJenisAttribute(): string
    {
        $labels = [
            'tools'      => 'Daftar Induk',
            'verifikasi' => 'Verifikasi',
            'internal'   => 'Internal',
            'eksternal'  => 'Eksternal Small',
            'mesin'      => 'Eksternal Mesin',
            'user'       => 'User',
        ];

        return $labels[$this->jenis_data] ?? $this->jenis_data;
    }
}