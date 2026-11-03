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

        // Filter verifikasi di SQL agar tidak menarik seluruh tabel ke PHP.
        $alatPerluPerhatian = static function () use ($tahunIni, $batasStandar) {
            return EksternalMesin::query()
                ->whereNotNull('next_date')
                ->where('next_date', '<=', $batasStandar)
                ->whereNotExists(function ($query) use ($tahunIni) {
                    $query->selectRaw('1')
                        ->from('verifikasis')
                        ->where('verifikasis.tahun', $tahunIni)
                        ->whereRaw('UPPER(TRIM(verifikasis.tool_name)) = UPPER(TRIM(eksternal_mesins.tool_name))');
                });
        };

        $jumlahTerlambat = (clone $alatPerluPerhatian())->where('next_date', '<', $hariIni)->count();
        $jumlahJatuhTempo7 = (clone $alatPerluPerhatian())->whereBetween('next_date', [$hariIni, $batasCepat])->count();
        $jumlahJatuhTempo30 = (clone $alatPerluPerhatian())->where('next_date', '>', $batasCepat)->count();

        $alatTerlambat = (clone $alatPerluPerhatian())->where('next_date', '<', $hariIni)
            ->orderBy('next_date')->limit(3)->get()->map(function ($item) use ($hariIni) {
                $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
                return $item;
            });
        $alatJatuhTempo7Hari = (clone $alatPerluPerhatian())->whereBetween('next_date', [$hariIni, $batasCepat])
            ->orderBy('next_date')->limit(3)->get()->map(function ($item) use ($hariIni) {
                $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
                return $item;
            });
        $alatJatuhTempo30Hari = (clone $alatPerluPerhatian())->where('next_date', '>', $batasCepat)
            ->orderBy('next_date')->limit(3)->get()->map(function ($item) use ($hariIni) {
                $item->sisa_hari = (int) $hariIni->diffInDays(Carbon::parse($item->next_date)->startOfDay(), false);
                return $item;
            });

        // Verifikasi dengan aktivitas yang belum dicatat pemeriksanya.
        $verifikasiBelumDikonfirmasiQuery = Verifikasi::query()
            ->where('tahun', $tahunIni)
            ->whereNull('verified_at')
            ->where('total', '>', 0);
        $jumlahVerifikasiBelumDikonfirmasi = (clone $verifikasiBelumDikonfirmasiQuery)->count();
        $verifikasiBelumDikonfirmasi = $verifikasiBelumDikonfirmasiQuery
            ->orderBy('tool_name')->limit(5)->get();

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

        $totalNotifikasi = $jumlahTerlambat + $jumlahJatuhTempo7;

        return [
            'tahun' => $tahunIni,
            'bulanLabel' => $bulanLabel,
            'progressKalibrasi' => $progressKalibrasi,
            'totalAlatKalibrasi' => $totalAlatKalibrasi,
            'totalKalibrasiBulanIni' => $totalKalibrasiBulanIni,
            'alatTerlambat' => $alatTerlambat,
            'alatJatuhTempo7Hari' => $alatJatuhTempo7Hari,
            'alatJatuhTempo30Hari' => $alatJatuhTempo30Hari,
            'jumlahTerlambat' => $jumlahTerlambat,
            'jumlahJatuhTempo7' => $jumlahJatuhTempo7,
            'jumlahJatuhTempo30' => $jumlahJatuhTempo30,
            'verifikasiBelumDikonfirmasi' => $verifikasiBelumDikonfirmasi,
            'jumlahVerifikasiBelumDikonfirmasi' => $jumlahVerifikasiBelumDikonfirmasi,
            'aktivitasMingguIni' => $aktivitasMingguIni,
            'aktivitasMingguLalu' => $aktivitasMingguLalu,
            'perubahanAktivitas' => $perubahanAktivitas,
            'breakdownKategori' => $breakdownKategori,
            'totalNotifikasi' => $totalNotifikasi,
            'warningCepat' => $warningCepat,
            'warningStandar' => $warningStandar,
        ];
    }
}
