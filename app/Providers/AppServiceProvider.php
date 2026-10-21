<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\EksternalMesin;
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

            $hariIni = Carbon::today()->startOfDay();
            $total = Cache::remember(
                'global-notifikasi:' . $hariIni->toDateString(),
                now()->addMinute(),
                function () use ($hariIni) {
                    $batasCepat = $hariIni->copy()
                        ->addDays(Pengaturan::warningCepat())
                        ->startOfDay();
                    $tahunIni = $hariIni->year;

                    return EksternalMesin::query()
                        ->whereNotNull('next_date')
                        ->where('next_date', '<=', $batasCepat)
                        ->whereNotExists(function ($query) use ($tahunIni) {
                            $query->selectRaw('1')
                                ->from('verifikasis')
                                ->where('verifikasis.tahun', $tahunIni)
                                ->whereRaw(
                                    'UPPER(TRIM(verifikasis.tool_name)) = UPPER(TRIM(eksternal_mesins.tool_name))'
                                );
                        })
                        ->count();
                }
            );

            $view->with('globalNotifikasi', $total);
        });
    }
}