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
        $filters = $request->validate(['tahun' => 'nullable|integer|between:2000,2100']);
        $tahun = $filters['tahun'] ?? date('Y');

        // ===== KARTU RINGKASAN =====
        $totalTools       = Tool::where('tahun', $tahun)->count();
        $totalVerifikasi  = Verifikasi::where('tahun', $tahun)->count();
        $totalInternal    = Internal::where('tahun', $tahun)->count();
        $totalEksternal   = Eksternal::where('tahun', $tahun)->count();
        $totalMesin       = EksternalMesin::where('tahun', $tahun)->count();

        // ===== KPI (dari KpiController) =====
        $kpi = KpiController::hitungSemuaKpi();

        // ===== DATA VIEW =====
        $viewData = compact(
            'tahun',
            'totalTools', 'totalVerifikasi', 'totalInternal', 'totalEksternal', 'totalMesin',
            'kpi'
        );

        if (Auth::check() && Auth::user()->role === 'admin') {
            return view('dashboard.index', $viewData);
        }

        return view('dashboard.user', $viewData);
    }
}