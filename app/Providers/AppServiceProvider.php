<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\EksternalMesin;
use App\Models\Pengaturan;
use App\Models\Verifikasi;
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

            $hariIni = Carbon::today()->startOfDay();
            $batasCepat = $hariIni->copy()
                        ->addDays(Pengaturan::warningCepat())
                        ->startOfDay();
            $tahunIni = $hariIni->year;

            $alatPerluTindakLanjut = EksternalMesin::query()
                        ->whereNotNull('next_date')
                        ->where('next_date', '<=', $batasCepat)
                        ->whereNotExists(function ($query) use ($tahunIni) {
                            $query->selectRaw('1')
                                ->from('verifikasis')
                                ->where('verifikasis.tahun', $tahunIni)
                                ->whereNotNull('verifikasis.verified_at')
                                ->whereRaw(
                                    'UPPER(TRIM(verifikasis.tool_name)) = UPPER(TRIM(eksternal_mesins.tool_name))'
                                );
                        })
                        ->count();

            $verifikasiBelumDikonfirmasi = Verifikasi::where('tahun', $tahunIni)
                        ->whereNull('verified_at')
                        ->where('total', '>', 0)
                        ->count();

            $total = $alatPerluTindakLanjut + $verifikasiBelumDikonfirmasi;

            $view->with('globalNotifikasi', $total);
        });
    }
}
