<?php

namespace App\Providers;

use App\Models\ExternalMachine;
use App\Models\Setting;
use App\Models\Verification;
use App\Services\ScheduleExpiryNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
                $view->with('globalNotification', 0);
                return;
            }

            $hariIni = Carbon::today()->startOfDay();
            $batasCepat = $hariIni->copy()
                ->addDays(Setting::warningCepat())
                ->startOfDay();
            $tahunIni = $hariIni->year;

            $alatPerluTindakLanjut = ExternalMachine::query()
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

            $verifikasiBelumDikonfirmasi = Verification::where('tahun', $tahunIni)
                ->whereNull('verified_at')
                ->where('total', '>', 0)
                ->count();

            $jadwalExpiredH5 = app(ScheduleExpiryNotifier::class)->dueItems($hariIni)->count();

            $total = $alatPerluTindakLanjut + $verifikasiBelumDikonfirmasi + $jadwalExpiredH5;

            $view->with('globalNotification', $total);
        });
    }
}