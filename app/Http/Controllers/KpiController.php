<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Internal;
use App\Models\Eksternal;
use App\Models\EksternalMesin;
use App\Models\ActivityLog;
use App\Models\Verifikasi;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    public static function hitungSemuaKpi(): array
    {
        $now = Carbon::now();
        $tahunIni = $now->year;
        $bulanLabel = $now->translatedFormat('F Y');

        // Pengaturan warning
        $warningCepat = Pengaturan::warningCepat();
        $warningStandar = Pengaturan::warningStandar();

        // ==========================================
        // KPI A: PROGRESS KALIBRASI BULAN INI
        // ==========================================
        $kolomBulan = [
            1 => 'jan', 2 => 'feb', 3 => 'mar', 4 => 'apr',
            5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'aug',
            9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dec'
        ];
        $kolom = $kolomBulan[$now->month] ?? 'jan';

        $totalAlatKalibrasi = Internal::where('tahun', $tahunIni)->count()
                             + Eksternal::where('tahun', $tahunIni)->count()
                             + EksternalMesin::where('tahun', $tahunIni)->count();

        $totalKalibrasiBulanIni = (Internal::where('tahun', $tahunIni)->sum($kolom) ?? 0)
                                + (Eksternal::where('tahun', $tahunIni)->sum($kolom) ?? 0)
                                + (EksternalMesin::where('tahun', $tahunIni)->sum($kolom) ?? 0);

        $progressKalibrasi = $totalAlatKalibrasi > 0
            ? min(round(($totalKalibrasiBulanIni / $totalAlatKalibrasi) * 100, 1), 100)
            : 0;

        // ==========================================
        // KPI B: ALAT TERLAMBAT & JATUH TEMPO
        // Filter: sembunyikan yang sudah ada di tabel verifikasis tahun ini
        // ==========================================
        $hariIni = Carbon::today()->startOfDay();
        $batasCepat = $hariIni->copy()->addDays($warningCepat)->startOfDay();
        $batasStandar = $hariIni->copy()->addDays($warningStandar)->startOfDay();

        // Nama alat yang sudah diverifikasi tahun ini (apapun tipenya, kolom manapun)
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
                // Sembunyikan kalau alat sudah ada di tabel verifikasis tahun ini
                $namaAlat = strtoupper(trim($item->tool_name));
                return !in_array($namaAlat, $alatSudahDiverifikasi);
            });

        // Alat TERLAMBAT
        $alatTerlambat = $semuaAlat->filter(function ($item) use ($hariIni) {
            return Carbon::parse($item->next_date)->startOfDay() < $hariIni;
        })->map(function ($item) use ($hariIni) {
            $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
            return $item;
        })->values();

        // Alat jatuh tempo ≤ warningCepat
        $alatJatuhTempo7Hari = $semuaAlat->filter(function ($item) use ($hariIni, $batasCepat) {
            $nextDate = Carbon::parse($item->next_date)->startOfDay();
            return $nextDate >= $hariIni && $nextDate <= $batasCepat;
        })->map(function ($item) use ($hariIni) {
            $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
            return $item;
        })->values();

        // Alat jatuh tempo antara warningCepat & warningStandar
        $alatJatuhTempo30Hari = $semuaAlat->filter(function ($item) use ($batasCepat, $batasStandar) {
            $nextDate = Carbon::parse($item->next_date)->startOfDay();
            return $nextDate > $batasCepat && $nextDate <= $batasStandar;
        })->map(function ($item) use ($hariIni) {
            $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
            return $item;
        })->values();

        // ==========================================
        // KPI C: AKTIVITAS MINGGU INI
        // ==========================================
        $awalMingguIni = $now->copy()->startOfWeek();
        $akhirMingguIni = $now->copy()->endOfWeek();
        $awalMingguLalu = $awalMingguIni->copy()->subWeek();
        $akhirMingguLalu = $akhirMingguIni->copy()->subWeek();

        $aktivitasMingguIni = ActivityLog::whereBetween('created_at', [$awalMingguIni, $akhirMingguIni])->count();
        $aktivitasMingguLalu = ActivityLog::whereBetween('created_at', [$awalMingguLalu, $akhirMingguLalu])->count();

        $perubahanAktivitas = $aktivitasMingguLalu > 0
            ? round((($aktivitasMingguIni - $aktivitasMingguLalu) / $aktivitasMingguLalu) * 100, 1)
            : ($aktivitasMingguIni > 0 ? 100 : 0);

        // ==========================================
        // BREAKDOWN
        // ==========================================
        $breakdownKategori = Tool::where('tahun', $tahunIni)
            ->select('category', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('category')
            ->get();

        $totalNotifikasi = $alatTerlambat->count() + $alatJatuhTempo7Hari->count();

        return [
            'tahun' => $tahunIni,
            'bulanLabel' => $bulanLabel,
            'progressKalibrasi' => $progressKalibrasi,
            'totalAlatKalibrasi' => $totalAlatKalibrasi,
            'totalKalibrasiBulanIni' => $totalKalibrasiBulanIni,
            'alatTerlambat' => $alatTerlambat,
            'alatJatuhTempo7Hari' => $alatJatuhTempo7Hari,
            'alatJatuhTempo30Hari' => $alatJatuhTempo30Hari,
            'jumlahTerlambat' => $alatTerlambat->count(),
            'jumlahJatuhTempo7' => $alatJatuhTempo7Hari->count(),
            'jumlahJatuhTempo30' => $alatJatuhTempo30Hari->count(),
            'aktivitasMingguIni' => $aktivitasMingguIni,
            'aktivitasMingguLalu' => $aktivitasMingguLalu,
            'perubahanAktivitas' => $perubahanAktivitas,
            'breakdownKategori' => $breakdownKategori,
            'totalTools' => Tool::where('tahun', $tahunIni)->count(),
            'totalVerifikasi' => Verifikasi::where('tahun', $tahunIni)->count(),
            'totalInternal' => Internal::where('tahun', $tahunIni)->count(),
            'totalEksternal' => Eksternal::where('tahun', $tahunIni)->count(),
            'totalMesin' => EksternalMesin::where('tahun', $tahunIni)->count(),
            'totalNotifikasi' => $totalNotifikasi,
            'warningCepat' => $warningCepat,
            'warningStandar' => $warningStandar,
        ];
    }
}