<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\EksternalMesin;
use App\Models\Verifikasi;
use App\Models\Pengaturan;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (!Auth::check()) {
                $view->with('globalNotifikasi', 0);
                return;
            }

            $warningCepat = Pengaturan::warningCepat();
            $hariIni = Carbon::today()->startOfDay();
            $batasCepat = $hariIni->copy()->addDays($warningCepat)->startOfDay();
            $tahunIni = Carbon::now()->year;

            // Nama alat yang sudah diverifikasi tahun ini
            $alatSudahDiverifikasi = Verifikasi::where('tahun', $tahunIni)
                ->pluck('tool_name')
                ->map(function ($name) {
                    return strtoupper(trim($name));
                })
                ->unique()
                ->toArray();

            // Hitung notifikasi
            $total = EksternalMesin::whereNotNull('next_date')
                ->where('next_date', '<=', $batasCepat)
                ->get()
                ->filter(function ($item) use ($alatSudahDiverifikasi) {
                    $namaAlat = strtoupper(trim($item->tool_name));
                    return !in_array($namaAlat, $alatSudahDiverifikasi);
                })
                ->count();

            $view->with('globalNotifikasi', $total);
        });
    }
}