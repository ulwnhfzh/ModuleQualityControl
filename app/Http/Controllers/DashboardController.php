<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\Verification;
use App\Models\Internal;
use App\Models\External;
use App\Models\ExternalMachine;
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
        $totalVerification  = Verification::where('tahun', $tahun)->count();
        $totalInternal    = Internal::where('tahun', $tahun)->count();
        $totalExternal   = External::where('tahun', $tahun)->count();
        $totalMachine       = ExternalMachine::where('tahun', $tahun)->count();

        // ===== KPI (dari KpiController) =====
        $kpi = KpiController::hitungSemuaKpi();

        // ===== DATA VIEW =====
        $viewData = compact(
            'tahun',
            'totalTools', 'totalVerification', 'totalInternal', 'totalExternal', 'totalMachine',
            'kpi'
        );

        if (Auth::check() && Auth::user()->role === 'admin') {
            return view('dashboard.summary', $viewData);
        }

        return view('dashboard.user', $viewData);
    }
}