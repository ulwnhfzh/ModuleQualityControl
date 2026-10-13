<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenCounter extends Model
{
    use HasFactory;

    protected $table = 'dokumen_counter';

    protected $fillable = [
        'jenis',
        'tahun',
        'nomor_terakhir',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'nomor_terakhir' => 'integer',
    ];

    /**
     * Generate nomor dokumen berikutnya
     *
     * @param string $jenis   Kode jenis (LAP, VFR, dll)
     * @param int $tahun      Tahun
     * @param string $prefix  Prefix sebelum tahun (default: QC)
     * @return string         Contoh: QC/LAP/2026/001
     */
    public static function generateNomor(string $jenis, int $tahun, string $prefix = 'QC'): string
    {
        // Cari atau buat counter untuk (jenis, tahun)
        $counter = self::firstOrCreate(
            ['jenis' => $jenis, 'tahun' => $tahun],
            ['nomor_terakhir' => 0]
        );

        // Increment
        $counter->nomor_terakhir += 1;
        $counter->save();

        // Format: QC/LAP/2026/001
        $nomorUrut = str_pad($counter->nomor_terakhir, 3, '0', STR_PAD_LEFT);

        return "{$prefix}/{$jenis}/{$tahun}/{$nomorUrut}";
    }
}