<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\VerifikasiPeriodeController;
use App\Http\Controllers\InternalController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LaporanHarianController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ToolDefectController; // <-- Tambahan Data NG

/*
|--------------------------------------------------------------------------
| ROUTE PUBLIK (Guest / Belum Login)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/view', [GuestController::class, 'index'])->name('guest.index');
Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
Route::get('/verifikasi/periode', [VerifikasiPeriodeController::class, 'index'])->name('verifikasi.periode.index');
Route::get('/internal', [InternalController::class, 'index'])->name('internal.index');
Route::get('/eksternal-small', [CalibrationController::class, 'eksternalSmall'])->name('eksternal.small');
Route::get('/mesin', [CalibrationController::class, 'mesin'])->name('mesin.index');

/*
|--------------------------------------------------------------------------
| ROUTE USER (Harus Login - Hanya View)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Laporan dan transaksi stok alat ukur
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/rekap-bulanan', [StockController::class, 'summary'])->name('stock.summary');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{alatId}/diproses', [NotifikasiController::class, 'tandaiDiproses'])->name('notifikasi.diproses');
    Route::post('/notifikasi/{alatId}/selesai', [NotifikasiController::class, 'tandaiSelesai'])->name('notifikasi.selesai');
    Route::post('/notifikasi/{alatId}/batal', [NotifikasiController::class, 'batal'])->name('notifikasi.batal');
});

/*
|--------------------------------------------------------------------------
| ROUTE ADMIN (Harus Login & Role = admin - Akses Penuh)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    // ===== STOCK =====
    Route::post('/stock/movements', [StockController::class, 'store'])->name('stock.movements.store');
    Route::post('/stock/grid', [StockController::class, 'storeGrid'])->name('stock.grid.store');
    Route::post('/stock/items', [StockController::class, 'storeItem'])->name('stock.items.store');
    Route::get('/stock/import', [StockController::class, 'importForm'])->name('stock.import.form');
    Route::post('/stock/import', [StockController::class, 'importMovements'])->name('stock.import');
    Route::get('/stock/export', [StockController::class, 'export'])->name('stock.export');

    // ===== DAFTAR INDUK =====
    Route::get('/tools/create', [ToolController::class, 'create'])->name('tools.create');
    Route::post('/tools', [ToolController::class, 'store'])->name('tools.store');
    Route::get('/tools/{tool}/edit', [ToolController::class, 'edit'])->name('tools.edit');
    Route::put('/tools/{tool}', [ToolController::class, 'update'])->name('tools.update');
    Route::delete('/tools/{tool}', [ToolController::class, 'destroy'])->name('tools.destroy');
    Route::get('/tools/import/form', [ToolController::class, 'importForm'])->name('tools.import.form');
    Route::post('/tools/import', [ToolController::class, 'importStore'])->name('tools.import.store');
    Route::get('/tools/export/csv', [ToolController::class, 'exportCsv'])->name('tools.export.csv');
    Route::get('/tools/export/pdf', [ToolController::class, 'exportPdf'])->name('tools.export.pdf');
    Route::get('/tools/download-template', [ToolController::class, 'downloadTemplate'])->name('tools.template');

    // ===== DATA NG (KERUSAKAN) =====
    Route::get('/defects', [ToolDefectController::class, 'index'])->name('defects.index');
    Route::get('/defects/create', [ToolDefectController::class, 'create'])->name('defects.create');
    Route::post('/defects', [ToolDefectController::class, 'store'])->name('defects.store');
    // Import & Export Data NG
    Route::get('/defects/import', [ToolDefectController::class, 'importForm'])->name('defects.import.form');
    Route::post('/defects/import', [ToolDefectController::class, 'importStore'])->name('defects.import.store');
    Route::get('/defects/export/excel', [ToolDefectController::class, 'exportExcel'])->name('defects.export.excel');
    Route::get('/defects/export/pdf', [ToolDefectController::class, 'exportPdf'])->name('defects.export.pdf');

    // ===== JADWAL VERIFIKASI =====
    Route::get('/verifikasi/create', [VerifikasiController::class, 'create'])->name('verifikasi.create');
    Route::get('/verifikasi/periode/create', [VerifikasiPeriodeController::class, 'create'])->name('verifikasi.periode.create');
    Route::get('/verifikasi/periode/import/form', [VerifikasiPeriodeController::class, 'importForm'])->name('verifikasi.periode.import.form');
    Route::post('/verifikasi/periode/import', [VerifikasiPeriodeController::class, 'import'])->name('verifikasi.periode.import');
    Route::get('/verifikasi/periode/export', [VerifikasiPeriodeController::class, 'export'])->name('verifikasi.periode.export');
    Route::get('/verifikasi/periode/kalibrasi/create', [VerifikasiPeriodeController::class, 'internalCalibrationCreate'])->name('verifikasi.periode.kalibrasi.create');
    Route::post('/verifikasi/periode/kalibrasi', [VerifikasiPeriodeController::class, 'internalCalibrationStore'])->name('verifikasi.periode.kalibrasi.store');
    Route::get('/verifikasi/periode/kalibrasi/import/form', [VerifikasiPeriodeController::class, 'internalCalibrationImportForm'])->name('verifikasi.periode.kalibrasi.import.form');
    Route::post('/verifikasi/periode/kalibrasi/import', [VerifikasiPeriodeController::class, 'internalCalibrationImport'])->name('verifikasi.periode.kalibrasi.import');
    Route::get('/verifikasi/periode/kalibrasi/export', [VerifikasiPeriodeController::class, 'internalCalibrationExport'])->name('verifikasi.periode.kalibrasi.export');
    Route::get('/verifikasi/periode/kalibrasi/{schedule}/edit', [VerifikasiPeriodeController::class, 'internalCalibrationEdit'])->name('verifikasi.periode.kalibrasi.edit');
    Route::put('/verifikasi/periode/kalibrasi/{schedule}', [VerifikasiPeriodeController::class, 'internalCalibrationUpdate'])->name('verifikasi.periode.kalibrasi.update');
    Route::delete('/verifikasi/periode/kalibrasi/{schedule}', [VerifikasiPeriodeController::class, 'internalCalibrationDestroy'])->name('verifikasi.periode.kalibrasi.destroy');
    Route::post('/verifikasi/periode', [VerifikasiPeriodeController::class, 'store'])->name('verifikasi.periode.store');
    Route::get('/verifikasi/periode/{schedule}/edit', [VerifikasiPeriodeController::class, 'edit'])->name('verifikasi.periode.edit');
    Route::put('/verifikasi/periode/{schedule}', [VerifikasiPeriodeController::class, 'update'])->name('verifikasi.periode.update');
    Route::delete('/verifikasi/periode/{schedule}', [VerifikasiPeriodeController::class, 'destroy'])->name('verifikasi.periode.destroy');
    Route::post('/verifikasi', [VerifikasiController::class, 'store'])->name('verifikasi.store');
    Route::get('/verifikasi/{verifikasi}/edit', [VerifikasiController::class, 'edit'])->name('verifikasi.edit');
    Route::put('/verifikasi/{verifikasi}', [VerifikasiController::class, 'update'])->name('verifikasi.update');
    Route::delete('/verifikasi/{verifikasi}', [VerifikasiController::class, 'destroy'])->name('verifikasi.destroy');
    Route::get('/verifikasi/import/form', [VerifikasiController::class, 'importForm'])->name('verifikasi.import.form');
    Route::post('/verifikasi/import', [VerifikasiController::class, 'importStore'])->name('verifikasi.import.store');
    Route::get('/verifikasi/export/excel', [VerifikasiController::class, 'exportExcel'])->name('verifikasi.export.excel');
    Route::get('/verifikasi/export/pdf', [VerifikasiController::class, 'exportPdf'])->name('verifikasi.export.pdf');
    Route::get('/verifikasi/download-template', [VerifikasiController::class, 'downloadTemplate'])->name('verifikasi.template');

    // ===== KALIBRASI INTERNAL =====
    Route::get('/internal/create', [InternalController::class, 'create'])->name('internal.create');
    Route::post('/internal', [InternalController::class, 'store'])->name('internal.store');
    Route::get('/internal/{internal}/edit', [InternalController::class, 'edit'])->name('internal.edit');
    Route::put('/internal/{internal}', [InternalController::class, 'update'])->name('internal.update');
    Route::delete('/internal/{internal}', [InternalController::class, 'destroy'])->name('internal.destroy');
    Route::get('/internal/import/form', [InternalController::class, 'importForm'])->name('internal.import.form');
    Route::post('/internal/import', [InternalController::class, 'importStore'])->name('internal.import.store');
    Route::get('/internal/export/excel', [InternalController::class, 'exportExcel'])->name('internal.export.excel');
    Route::get('/internal/export/pdf', [InternalController::class, 'exportPdf'])->name('internal.export.pdf');
    Route::get('/internal/download-template', [InternalController::class, 'downloadTemplate'])->name('internal.template');

    // ===== KALIBRASI EKSTERNAL SMALL =====
    Route::get('/eksternal-small/create', [CalibrationController::class, 'eksternalSmallCreate'])->name('eksternal.small.create');
    Route::post('/eksternal-small', [CalibrationController::class, 'eksternalSmallStore'])->name('eksternal.small.store');
    Route::get('/eksternal-small/{eksternal}/edit', [CalibrationController::class, 'eksternalSmallEdit'])->name('eksternal.small.edit');
    Route::put('/eksternal-small/{eksternal}', [CalibrationController::class, 'eksternalSmallUpdate'])->name('eksternal.small.update');
    Route::delete('/eksternal-small/{eksternal}', [CalibrationController::class, 'eksternalSmallDestroy'])->name('eksternal.small.destroy');
    Route::get('/eksternal-small/import/form', [CalibrationController::class, 'eksternalSmallImportForm'])->name('eksternal.small.import.form');
    Route::post('/eksternal-small/import', [CalibrationController::class, 'eksternalSmallImportStore'])->name('eksternal.small.import.store');
    Route::get('/eksternal-small/export/excel', [CalibrationController::class, 'eksternalSmallExportExcel'])->name('eksternal.small.export.excel');
    Route::get('/eksternal-small/export/pdf', [CalibrationController::class, 'eksternalSmallExportPdf'])->name('eksternal.small.export.pdf');
    Route::get('/eksternal-small/download-template', [CalibrationController::class, 'eksternalSmallDownloadTemplate'])->name('eksternal.small.template');

    // ===== KALIBRASI EKSTERNAL MESIN =====
    Route::get('/mesin/create', [CalibrationController::class, 'mesinCreate'])->name('mesin.create');
    Route::post('/mesin', [CalibrationController::class, 'mesinStore'])->name('mesin.store');
    Route::get('/mesin/{mesin}/edit', [CalibrationController::class, 'mesinEdit'])->name('mesin.edit');
    Route::put('/mesin/{mesin}', [CalibrationController::class, 'mesinUpdate'])->name('mesin.update');
    Route::delete('/mesin/{mesin}', [CalibrationController::class, 'mesinDestroy'])->name('mesin.destroy');
    Route::get('/mesin/import/form', [CalibrationController::class, 'mesinImportForm'])->name('mesin.import.form');
    Route::post('/mesin/import', [CalibrationController::class, 'mesinImportStore'])->name('mesin.import.store');
    Route::get('/mesin/export/excel', [CalibrationController::class, 'mesinExportExcel'])->name('mesin.export.excel');
    Route::get('/mesin/export/pdf', [CalibrationController::class, 'mesinExportPdf'])->name('mesin.export.pdf');
    Route::get('/mesin/download-template', [CalibrationController::class, 'mesinDownloadTemplate'])->name('mesin.template');

    // ===== REPORTS =====
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    
    // --- API GRAFIK PARETO MANDIRI ---
    Route::get('/reports/api/pareto-fitur', [ReportController::class, 'apiParetoFitur'])->name('reports.api.pareto.fitur');
    Route::get('/reports/api/pareto-kategori', [ReportController::class, 'apiParetoKategori'])->name('reports.api.pareto.kategori');
    Route::get('/reports/api/pareto-bulanan', [ReportController::class, 'apiParetoBulanan'])->name('reports.api.pareto.bulanan');
    Route::get('/reports/api/pareto-departemen', [ReportController::class, 'apiParetoDepartemen'])->name('reports.api.pareto.departemen');

    Route::post('/reports/export-all/excel', [ReportController::class, 'exportAllExcel'])->name('reports.export.all.excel');
    Route::post('/reports/export-all/pdf', [ReportController::class, 'exportAllPdf'])->name('reports.export.all.pdf');
    Route::get('/reports/export-log/excel', [ReportController::class, 'exportLogExcel'])->name('reports.export.log.excel');
    Route::get('/reports/export-log/pdf', [ReportController::class, 'exportLogPdf'])->name('reports.export.log.pdf');

    // ===== LAPORAN HARIAN =====
    Route::get('/laporan-harian', [LaporanHarianController::class, 'index'])->name('laporan.harian.index');
    Route::get('/laporan-harian/preview-nomor', [LaporanHarianController::class, 'previewNomor'])->name('laporan.harian.preview');
    Route::get('/laporan-harian/export-pdf', [LaporanHarianController::class, 'exportPdf'])->name('laporan.harian.pdf');
    Route::get('/laporan-harian/export-excel', [LaporanHarianController::class, 'exportExcel'])->name('laporan.harian.excel');

    // ===== SETTINGS =====
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/user/create', [SettingController::class, 'createUser'])->name('settings.user.create');
    Route::post('/settings/user', [SettingController::class, 'storeUser'])->name('settings.user.store');
    Route::get('/settings/user/{user}/edit', [SettingController::class, 'editUser'])->name('settings.user.edit');
    Route::put('/settings/user/{user}', [SettingController::class, 'updateUser'])->name('settings.user.update');
    Route::delete('/settings/user/{user}', [SettingController::class, 'destroyUser'])->name('settings.user.destroy');

    // ===== PENGATURAN NOTIFIKASI =====
    Route::get('/settings/notifikasi', [PengaturanController::class, 'notifikasi'])->name('settings.notifikasi');
    Route::post('/settings/notifikasi', [PengaturanController::class, 'updateNotifikasi'])->name('settings.notifikasi.update');
});

require __DIR__.'/auth.php';