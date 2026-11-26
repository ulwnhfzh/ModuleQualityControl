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
    $summary = app(ReminderService::class)->run(Carbon::today(), $dryRun);

    if ($summary['no_admin']) {
        $this->warn('Tidak ada akun admin dengan email.');
        return;
    }

    if ($summary['already_today'] > 0) {
        $this->line('Email harian hari ini sudah terkirim.');
        return;
    }

    $this->info('Jadwal yang diperiksa: ' . $summary['items']);
    $this->info(($dryRun ? 'Akan dikirim: ' : 'Terkirim: ') . $summary['sent']);
    $this->line('Sudah diisi (pengingat dihentikan): ' . $summary['done']);
})->purpose('Kirim pengingat email verifikasi dan kalibrasi setiap hari sampai periode diisi.');

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

Schedule::command('verifikasi:reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();