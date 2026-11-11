<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\ExternalMachine;
use App\Models\User;
use App\Models\Verification;
use App\Models\Setting;
use App\Notifications\VerificationDueDateNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('verifikasi:reminders', function (): void {
    $today = Carbon::today();
    $limit = $today->copy()->addDays(Setting::warningStandar());
    $admins = User::where('role', 'admin')->whereNotNull('email')->get();
    if ($admins->isEmpty()) {
        $this->warn('Tidak ada akun admin dengan email.');
        return;
    }

    $confirmedNames = Verification::where('tahun', $today->year)
        ->whereNotNull('verified_at')
        ->pluck('tool_name')
        ->map(fn ($name) => mb_strtoupper(trim($name)))
        ->unique()
        ->all();
    $sent = 0;

    ExternalMachine::whereNotNull('next_date')
        ->whereDate('next_date', '<=', $limit->toDateString())
        ->orderBy('id')
        ->chunkById(200, function ($tools) use ($today, $confirmedNames, $admins, &$sent): void {
            foreach ($tools as $tool) {
                if (in_array(mb_strtoupper(trim($tool->tool_name)), $confirmedNames, true)) {
                    continue;
                }

                $nextDate = Carbon::parse($tool->next_date)->startOfDay();
                $level = $nextDate->lt($today) ? 'terlambat' : 'mendekati';
                $days = (int) $today->diffInDays($nextDate, false);
                $logged = DB::table('verification_reminder_logs')
                    ->where('eksternal_mesin_id', $tool->id)
                    ->whereDate('next_date', $nextDate->toDateString())
                    ->where('reminder_level', $level)
                    ->exists();
                if ($logged) {
                    continue;
                }

                Notification::send($admins, new VerificationDueDateNotification(
                    $tool->tool_name,
                    $nextDate->toDateString(),
                    $level,
                    $days,
                ));
                DB::table('verification_reminder_logs')->insert([
                    'eksternal_mesin_id' => $tool->id,
                    'next_date' => $nextDate->toDateString(),
                    'reminder_level' => $level,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $sent++;
            }
        });

    $this->info("Pengingat email terkirim untuk {$sent} alat.");
})->purpose('Kirim pengingat email verifikasi mendekati tenggat dan terlambat.');

Schedule::command('verifikasi:reminders')->dailyAt('07:00')->withoutOverlapping();
