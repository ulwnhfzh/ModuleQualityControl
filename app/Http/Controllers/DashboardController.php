<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboard)
    {
        $filters = $request->validate(['tahun' => 'nullable|integer|between:2000,2100']);
        $tahun = (int) ($filters['tahun'] ?? date('Y'));
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        $data = $dashboard->build($tahun);
        $data['aktivitas'] = $isAdmin ? $dashboard->aktivitas() : [];

        return view('dashboard.summary', array_merge($data, [
            'tahun' => $tahun,
            'isAdmin' => $isAdmin,
            'defaultPareto' => 'belum_selesai',
        ]));
    }
}