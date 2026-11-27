<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Imports\ProductionDataFolderImport;
use App\Services\ReminderService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('verifikasi:reminders {--dry-run : Tampilkan hasil tanpa mengirim email}', function (): void {
    $dryRun = (bool) $this->option('dry-run');
    $summary = app(ReminderService::class)->runDaily(Carbon::today(), $dryRun);

    if ($summary['no_admin']) {
        $this->warn('Tidak ada penerima email. Isi pengaturan reminder_emails atau tambahkan admin dengan email.');
        return;
    }

    if ($summary['already_today'] > 0) {
        $this->line('Email harian hari ini sudah terkirim.');
        return;
    }

    $this->info('Item yang diperiksa: ' . $summary['items']);
    $this->info(($dryRun ? 'Akan dimuat di email: ' : 'Dimuat di email: ') . $summary['sent']);
    $this->line('Sudah diisi (tidak diingatkan lagi): ' . $summary['done']);
})->purpose('Kirim email pengingat harian verifikasi dan kalibrasi.');

Artisan::command('verifikasi:rekap-mingguan {--dry-run : Tampilkan hasil tanpa mengirim email}', function (): void {
    $dryRun = (bool) $this->option('dry-run');
    $summary = app(ReminderService::class)->runWeekly(Carbon::today(), $dryRun);

    if ($summary['no_admin']) {
        $this->warn('Tidak ada penerima email.');
        return;
    }

    if ($summary['already_today']) {
        $this->line('Rekap mingguan hari ini sudah terkirim.');
        return;
    }

    $this->info('Jadwal terlambat lebih dari 90 hari: ' . $summary['count']);
    if (! $dryRun) {
        $this->info('Terkirim ke penerima: ' . $summary['sent']);
    }
})->purpose('Kirim rekap mingguan jadwal terlambat lebih dari 90 hari, dengan lampiran Excel.');

Artisan::command('data-all-produksi:import-folder {path}', function (string $path): void {
    $summary = app(ProductionDataFolderImport::class)->import($path);

    $this->info("Workbook diperiksa: {$summary['files_seen']}");
    $this->info("Workbook berhasil diimpor: {$summary['files_imported']}");
    $this->info("Baris diproses: {$summary['records_processed']}");

    foreach ($summary['categories'] as $category => $counts) {
        $this->line("{$category}: {$counts['files']} workbook, {$counts['records']} baris");
    }

    foreach ($summary['skipped'] as $file) {
        $this->warn("Dilewati: {$file}");
    }
})->purpose('Import workbook Data All Produksi secara rekursif dengan kategori dari nama folder.');

// Email harian: 08:00 WIB, dengan cadangan 12:00 WIB jika pengiriman pagi gagal.
Schedule::command('verifikasi:reminders')
    ->dailyAt('08:00')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('verifikasi:reminders')
    ->dailyAt('12:00')->timezone('Asia/Jakarta')->withoutOverlapping();

// Rekap mingguan: Senin 08:00 WIB, dengan cadangan 12:00 WIB.
Schedule::command('verifikasi:rekap-mingguan')
    ->weeklyOn(1, '08:00')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('verifikasi:rekap-mingguan')
    ->weeklyOn(1, '12:00')->timezone('Asia/Jakarta')->withoutOverlapping();