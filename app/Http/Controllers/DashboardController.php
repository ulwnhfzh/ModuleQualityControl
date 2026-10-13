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

        // ===== KARTU RINGKASAN =====
        $totalTools       = Tool::where('tahun', $tahun)->count();
        $totalVerifikasi  = Verifikasi::where('tahun', $tahun)->count();
        $totalInternal    = Internal::where('tahun', $tahun)->count();
        $totalEksternal   = Eksternal::where('tahun', $tahun)->count();
        $totalMesin       = EksternalMesin::where('tahun', $tahun)->count();

        // Distribusi Kategori
        $kategoriSmall   = Tool::where('tahun', $tahun)->where('category', 'Small Tool')->count();
        $kategoriMachine = Tool::where('tahun', $tahun)->where('category', 'Machine')->count();

        // ===== DATA BULANAN =====
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

        // ===== DATA TAHUNAN (5 tahun terakhir) =====
        $tahunRange = range(date('Y'), date('Y') - 4);
        sort($tahunRange);
        $tahunLabels = array_map('strval', $tahunRange);

        $verifikasiPerTahun = [];
        $internalPerTahun   = [];
        $eksternalPerTahun  = [];
        $mesinPerTahun      = [];
        $toolsPerTahun      = [];

        foreach ($tahunRange as $th) {
            $verifikasiPerTahun[] = Verifikasi::where('tahun', $th)->count();
            $internalPerTahun[]   = Internal::where('tahun', $th)->count();
            $eksternalPerTahun[]  = Eksternal::where('tahun', $th)->count();
            $mesinPerTahun[]      = EksternalMesin::where('tahun', $th)->count();
            $toolsPerTahun[]      = Tool::where('tahun', $th)->count();
        }

        // Distribusi Kategori untuk Doughnut (jika masih dipakai)
        $kategoriData = [
            'Small Tool' => $kategoriSmall,
            'Machine'    => $kategoriMachine,
        ];

        $viewData = compact(
            'tahun',
            'totalTools', 'totalVerifikasi', 'totalInternal', 'totalEksternal', 'totalMesin',
            'kategoriSmall', 'kategoriMachine', 'kategoriData',
            'bulanLabels', 'verifikasiPerBulan', 'internalPerBulan', 'eksternalPerBulan', 'mesinPerBulan',
            'tahunLabels', 'verifikasiPerTahun', 'internalPerTahun', 'eksternalPerTahun', 'mesinPerTahun', 'toolsPerTahun'
        );

        if (Auth::check() && Auth::user()->role === 'admin') {
            return view('dashboard.index', $viewData);
        }

        return view('dashboard.user', $viewData);
    }
}