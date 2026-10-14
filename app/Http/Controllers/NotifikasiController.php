<?php

namespace App\Http\Controllers;

use App\Models\EksternalMesin;
use App\Models\NotifikasiStatus;
use App\Models\Pengaturan;
use App\Models\Verifikasi;
use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $warningCepat = Pengaturan::warningCepat();
        $warningStandar = Pengaturan::warningStandar();

        $hariIni = Carbon::today()->startOfDay();
        $batasCepat = $hariIni->copy()->addDays($warningCepat)->startOfDay();
        $batasStandar = $hariIni->copy()->addDays($warningStandar)->startOfDay();

        $tahunIni = Carbon::now()->year;

        // Nama alat yang sudah diverifikasi tahun ini
        $alatSudahDiverifikasi = Verifikasi::where('tahun', $tahunIni)
            ->pluck('tool_name')
            ->map(function ($name) {
                return strtoupper(trim($name));
            })
            ->unique()
            ->toArray();

        // Ambil semua alat yang butuh perhatian
        $semuaAlat = EksternalMesin::whereNotNull('next_date')
            ->where('next_date', '<=', $batasStandar)
            ->get()
            ->filter(function ($item) use ($alatSudahDiverifikasi) {
                $namaAlat = strtoupper(trim($item->tool_name));
                return !in_array($namaAlat, $alatSudahDiverifikasi);
            });

        // Status notifikasi
        $statusMap = NotifikasiStatus::whereIn('alat_id', $semuaAlat->pluck('id'))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy('alat_id')
            ->map(function ($items) {
                return $items->first();
            });

        // Alat TERLAMBAT
        $alatTerlambat = $semuaAlat->filter(function ($item) use ($hariIni) {
            return Carbon::parse($item->next_date)->startOfDay() < $hariIni;
        })->map(function ($item) use ($hariIni, $statusMap) {
            $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
            $item->status_notifikasi = $statusMap->get($item->id);
            return $item;
        })->values();

        // Alat jatuh tempo ≤ warningCepat
        $alatJatuhTempo7 = $semuaAlat->filter(function ($item) use ($hariIni, $batasCepat) {
            $nextDate = Carbon::parse($item->next_date)->startOfDay();
            return $nextDate >= $hariIni && $nextDate <= $batasCepat;
        })->map(function ($item) use ($hariIni, $statusMap) {
            $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
            $item->status_notifikasi = $statusMap->get($item->id);
            return $item;
        })->values();

        // Alat jatuh tempo antara warningCepat & warningStandar
        $alatJatuhTempo30 = $semuaAlat->filter(function ($item) use ($batasCepat, $batasStandar) {
            $nextDate = Carbon::parse($item->next_date)->startOfDay();
            return $nextDate > $batasCepat && $nextDate <= $batasStandar;
        })->map(function ($item) use ($hariIni, $statusMap) {
            $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
            $item->status_notifikasi = $statusMap->get($item->id);
            return $item;
        })->values();

        return view('notifikasi.index', compact(
            'alatTerlambat',
            'alatJatuhTempo7',
            'alatJatuhTempo30',
            'hariIni',
            'warningCepat',
            'warningStandar'
        ));
    }

    public function tandaiDiproses(Request $request, $alatId)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:255',
        ]);

        $alat = EksternalMesin::findOrFail($alatId);

        $existing = NotifikasiStatus::where('alat_id', $alatId)
            ->where('status', 'diproses')
            ->first();

        if ($existing) {
            return back()->with('error', 'Alat ini sudah ditandai sedang disiapkan.');
        }

        $status = NotifikasiStatus::create([
            'alat_id' => $alat->id,
            'alat_nama' => $alat->tool_name,
            'next_date' => $alat->next_date,
            'status' => 'diproses',
            'catatan' => $request->catatan,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'Guest',
            'diproses_at' => now(),
        ]);

        $this->logActivity(
            'UPDATE',
            'notifikasi',
            $status->id,
            'Tandai diproses: ' . $alat->tool_name . ' - Catatan: ' . ($request->catatan ?? '-'),
            null,
            $status->toArray()
        );

        return back()->with('success', 'Alat berhasil ditandai sedang disiapkan.');
    }

    public function tandaiSelesai(Request $request, $alatId)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:255',
        ]);

        $alat = EksternalMesin::findOrFail($alatId);

        $status = NotifikasiStatus::where('alat_id', $alatId)
            ->where('status', 'diproses')
            ->first();

        if (!$status) {
            return back()->with('error', 'Alat ini belum ditandai diproses.');
        }

        $status->update([
            'status' => 'selesai',
            'catatan' => $request->catatan ?? $status->catatan,
            'selesai_at' => now(),
        ]);

        $this->logActivity(
            'UPDATE',
            'notifikasi',
            $status->id,
            'Selesai: ' . $alat->tool_name . ' - Catatan: ' . ($request->catatan ?? '-'),
            null,
            $status->fresh()->toArray()
        );

        return back()->with('success', 'Alat berhasil ditandai selesai.');
    }

    public function batal(Request $request, $alatId)
    {
        $request->validate([
            'catatan' => 'required|string|max:255',
        ]);

        $alat = EksternalMesin::findOrFail($alatId);

        $status = NotifikasiStatus::where('alat_id', $alatId)
            ->where('status', 'diproses')
            ->first();

        if (!$status) {
            return back()->with('error', 'Alat ini belum ditandai diproses.');
        }

        $status->update([
            'status' => 'dibatalkan',
            'catatan' => $request->catatan,
        ]);

        $this->logActivity(
            'UPDATE',
            'notifikasi',
            $status->id,
            'Dibatalkan: ' . $alat->tool_name . ' - Alasan: ' . $request->catatan,
            null,
            $status->fresh()->toArray()
        );

        return back()->with('success', 'Status notifikasi berhasil dibatalkan.');
    }
}