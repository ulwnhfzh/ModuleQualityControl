<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Verifikasi;
use App\Models\Internal;
use App\Models\Eksternal;
use App\Models\EksternalMesin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        // Kartu Ringkasan
        $totalTools       = Tool::where('tahun', $tahun)->count();
        $totalVerifikasi  = Verifikasi::where('tahun', $tahun)->count();
        $totalInternal    = Internal::where('tahun', $tahun)->count();
        $totalEksternal   = Eksternal::where('tahun', $tahun)->count();
        $totalMesin       = EksternalMesin::where('tahun', $tahun)->count();

        // Distribusi Kategori
        $kategoriSmall   = Tool::where('tahun', $tahun)->where('category', 'Small Tool')->count();
        $kategoriMachine = Tool::where('tahun', $tahun)->where('category', 'Machine')->count();

        // ===== DATA GRAFIK PER BULAN =====
        // Hanya dari tabel yang punya kolom bulan (verifikasis, internals, eksternals, eksternal_mesins)
        // Tabel `tools` TIDAK punya kolom bulan, jadi dikecualikan
        $bulanLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        $verifikasiPerBulan = [];
        $internalPerBulan   = [];
        $eksternalPerBulan  = [];
        $mesinPerBulan      = [];

        foreach ($bulanColumns as $col) {
            $verifikasiPerBulan[] = (int) Verifikasi::where('tahun', $tahun)->sum($col);
            $internalPerBulan[]   = (int) Internal::where('tahun', $tahun)->sum($col);
            $eksternalPerBulan[]  = (int) Eksternal::where('tahun', $tahun)->sum($col);
            $mesinPerBulan[]      = (int) EksternalMesin::where('tahun', $tahun)->sum($col);
        }

        // Grafik Per Kategori (Doughnut)
        $kategoriData = [
            'Small Tool' => $kategoriSmall,
            'Machine'    => $kategoriMachine,
        ];

        // Grafik Distribusi (Horizontal Bar)
        $distribusi = [
            'Daftar Induk' => $totalTools,
            'Verifikasi'   => $totalVerifikasi,
            'Internal'     => $totalInternal,
            'Eksternal'    => $totalEksternal,
            'Mesin'        => $totalMesin,
        ];

        // Cek Role untuk menentukan View
        if (Auth::check() && Auth::user()->role === 'admin') {
            return view('dashboard.index', compact(
                'tahun', 'totalTools', 'totalVerifikasi', 'totalInternal',
                'totalEksternal', 'totalMesin', 'kategoriSmall', 'kategoriMachine',
                'bulanLabels', 'verifikasiPerBulan',
                'internalPerBulan', 'eksternalPerBulan', 'mesinPerBulan',
                'kategoriData', 'distribusi'
            ));
        }

        return view('dashboard.user', compact(
            'tahun', 'totalTools', 'totalVerifikasi', 'totalInternal',
            'totalEksternal', 'totalMesin', 'kategoriSmall', 'kategoriMachine',
            'bulanLabels', 'verifikasiPerBulan',
            'internalPerBulan', 'eksternalPerBulan', 'mesinPerBulan',
            'kategoriData', 'distribusi'
        ));
    }
}