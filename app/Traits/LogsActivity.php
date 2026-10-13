<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Catat aktivitas ke tabel activity_logs
     *
     * @param string $aksi         CREATE, UPDATE, DELETE, LOGIN, LOGOUT, EXPORT
     * @param string|null $jenis   tools, verifikasi, internal, eksternal, mesin, user
     * @param int|null $dataId     ID data yang diubah
     * @param string|null $ket     Keterangan singkat
     * @param array|null $lama     Data sebelum (untuk update/delete)
     * @param array|null $baru     Data sesudah (untuk create/update)
     */
    protected function logActivity(
        string $aksi,
        ?string $jenis = null,
        ?int $dataId = null,
        ?string $ket = null,
        ?array $lama = null,
        ?array $baru = null
    ): void {
        try {
            $user = Auth::user();

            ActivityLog::create([
                'user_id'    => $user?->id,
                'user_name'  => $user?->name ?? 'Guest',
                'aksi'       => $aksi,
                'jenis_data' => $jenis,
                'data_id'    => $dataId,
                'keterangan' => $ket,
                'data_lama'  => $lama,
                'data_baru'  => $baru,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
            ]);
        } catch (\Exception $e) {
            // Jangan sampai log bikin aplikasi error
            // Pakai helper logger() supaya kompatibel di semua editor
            logger()->error('LogActivity Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Shortcut: Log CREATE
     */
    protected function logCreate(string $jenis, int $dataId, string $ket, array $dataBaru = []): void
    {
        $this->logActivity('CREATE', $jenis, $dataId, $ket, null, $dataBaru);
    }

    /**
     * Shortcut: Log UPDATE
     */
    protected function logUpdate(string $jenis, int $dataId, string $ket, array $dataLama = [], array $dataBaru = []): void
    {
        $this->logActivity('UPDATE', $jenis, $dataId, $ket, $dataLama, $dataBaru);
    }

    /**
     * Shortcut: Log DELETE
     */
    protected function logDelete(string $jenis, int $dataId, string $ket, array $dataLama = []): void
    {
        $this->logActivity('DELETE', $jenis, $dataId, $ket, $dataLama, null);
    }

    /**
     * Shortcut: Log EXPORT
     */
    protected function logExport(string $jenis, string $ket): void
    {
        $this->logActivity('EXPORT', $jenis, null, $ket, null, null);
    }

    /**
     * Shortcut: Log LOGIN
     */
    protected function logLogin(string $ket = 'User login'): void
    {
        $this->logActivity('LOGIN', 'user', Auth::id(), $ket, null, null);
    }

    /**
     * Shortcut: Log LOGOUT
     */
    protected function logLogout(string $ket = 'User logout'): void
    {
        $this->logActivity('LOGOUT', 'user', Auth::id(), $ket, null, null);
    }
}