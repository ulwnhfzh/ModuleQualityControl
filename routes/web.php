<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\VerificationPeriodController;
use App\Http\Controllers\InternalController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ToolDefectController;
use App\Http\Controllers\ProductionDataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MasterCalibratorController;
use App\Http\Controllers\ExternalCalibrationListUpController;

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
Route::get('/verifikasi', [VerificationController::class, 'index'])->name('verifikasi.index');
Route::get('/verifikasi/periode', [VerificationPeriodController::class, 'index'])->name('verifikasi.periode.index');
Route::get('/internal', [InternalController::class, 'index'])->name('internal.index');
Route::get('/eksternal-small', [CalibrationController::class, 'eksternalSmall'])->name('eksternal.small');
Route::get('/mesin', [CalibrationController::class, 'mesin'])->name('mesin.index');
Route::get('/master-kalibrator', [MasterCalibratorController::class, 'index'])->name('master-calibrator.index');
Route::get('/external-calibration-list-up', [ExternalCalibrationListUpController::class, 'index'])->name('external-list-up.index');

/*
|--------------------------------------------------------------------------
| ROUTE USER (Harus Login - Hanya View)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/rekap-bulanan', [StockController::class, 'summary'])->name('stock.summary');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/{alatId}/diproses', [NotificationController::class, 'tandaiDiproses'])->name('notifikasi.diproses');
    Route::post('/notifikasi/{alatId}/selesai', [NotificationController::class, 'tandaiSelesai'])->name('notifikasi.selesai');
    Route::post('/notifikasi/{alatId}/batal', [NotificationController::class, 'batal'])->name('notifikasi.batal');
});

/*
|--------------------------------------------------------------------------
| ROUTE ADMIN (Harus Login & Role = admin - Akses Penuh)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    // ===== DATA ALL PRODUKSI (FITUR BARU) =====
    Route::get('/data-all-produksi/get-tools', [ProductionDataController::class, 'getToolsByDivision'])->name('data-all-produksi.get-tools');
    Route::get('/data-all-produksi/export', [ProductionDataController::class, 'export'])->name('data-all-produksi.export');
    Route::get('/data-all-produksi/import/form', [ProductionDataController::class, 'importForm'])->name('data-all-produksi.import.form');
    Route::post('/data-all-produksi/import', [ProductionDataController::class, 'importStore'])->name('data-all-produksi.import.store');
    
    Route::resource('data-all-produksi', ProductionDataController::class);

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
    Route::get('/defects/import', [ToolDefectController::class, 'importForm'])->name('defects.import.form');
    Route::post('/defects/import', [ToolDefectController::class, 'importStore'])->name('defects.import.store');
    Route::get('/defects/export/excel', [ToolDefectController::class, 'exportExcel'])->name('defects.export.excel');
    Route::get('/defects/export/pdf', [ToolDefectController::class, 'exportPdf'])->name('defects.export.pdf');

    // TAMBAHAN BARU: ROUTE EDIT, UPDATE, DAN HAPUS DATA NG
    Route::get('/defects/{defect}/edit', [ToolDefectController::class, 'edit'])->name('defects.edit');
    Route::put('/defects/{defect}', [ToolDefectController::class, 'update'])->name('defects.update');
    Route::delete('/defects/{defect}', [ToolDefectController::class, 'destroy'])->name('defects.destroy');

    // ===== JADWAL VERIFIKASI (Tahunan/Bulanan) =====
    Route::get('/verifikasi/create', [VerificationController::class, 'create'])->name('verifikasi.create');
    Route::post('/verifikasi', [VerificationController::class, 'store'])->name('verifikasi.store');
    Route::get('/verifikasi/{verifikasi}/edit', [VerificationController::class, 'edit'])->name('verifikasi.edit');
    Route::post('/verifikasi/{verifikasi}/confirm', [VerificationController::class, 'confirm'])->name('verifikasi.confirm');
    Route::post('/verifikasi/{verifikasi}/reopen', [VerificationController::class, 'reopen'])->name('verifikasi.reopen');
    Route::put('/verifikasi/{verifikasi}', [VerificationController::class, 'update'])->name('verifikasi.update');
    Route::delete('/verifikasi/{verifikasi}', [VerificationController::class, 'destroy'])->name('verifikasi.destroy');
    Route::get('/verifikasi/import/form', [VerificationController::class, 'importForm'])->name('verifikasi.import.form');
    Route::post('/verifikasi/import', [VerificationController::class, 'importStore'])->name('verifikasi.import.store');
    Route::get('/verifikasi/export/excel', [VerificationController::class, 'exportExcel'])->name('verifikasi.export.excel');
    Route::get('/verifikasi/export/pdf', [VerificationController::class, 'exportPdf'])->name('verifikasi.export.pdf');
    Route::get('/verifikasi/download-template', [VerificationController::class, 'downloadTemplate'])->name('verifikasi.template');

    // ===== LIST UP PERIODE (Verification Internal & Kalibrasi Internal) =====
    Route::get('/verifikasi/periode/create', [VerificationPeriodController::class, 'create'])->name('verifikasi.periode.create');
    Route::get('/verifikasi/periode/import/form', [VerificationPeriodController::class, 'importForm'])->name('verifikasi.periode.import.form');
    Route::post('/verifikasi/periode/import', [VerificationPeriodController::class, 'import'])->name('verifikasi.periode.import');
    Route::get('/verifikasi/periode/export', [VerificationPeriodController::class, 'export'])->name('verifikasi.periode.export');
    Route::get('/verifikasi/periode/export/pdf', [VerificationPeriodController::class, 'exportPdf'])->name('verifikasi.periode.export.pdf');
    Route::post('/verifikasi/periode', [VerificationPeriodController::class, 'store'])->name('verifikasi.periode.store');
    Route::get('/verifikasi/periode/{schedule}/edit', [VerificationPeriodController::class, 'edit'])->name('verifikasi.periode.edit');
    Route::put('/verifikasi/periode/{schedule}', [VerificationPeriodController::class, 'update'])->name('verifikasi.periode.update');
    Route::delete('/verifikasi/periode/{schedule}', [VerificationPeriodController::class, 'destroy'])->name('verifikasi.periode.destroy');

    // ===== LIST UP PERIODE: KALIBRASI INTERNAL =====
    Route::get('/verifikasi/periode/kalibrasi/create', [VerificationPeriodController::class, 'internalCalibrationCreate'])->name('verifikasi.periode.kalibrasi.create');
    Route::post('/verifikasi/periode/kalibrasi', [VerificationPeriodController::class, 'internalCalibrationStore'])->name('verifikasi.periode.kalibrasi.store');
    Route::get('/verifikasi/periode/kalibrasi/{schedule}/edit', [VerificationPeriodController::class, 'internalCalibrationEdit'])->name('verifikasi.periode.kalibrasi.edit');
    Route::put('/verifikasi/periode/kalibrasi/{schedule}', [VerificationPeriodController::class, 'internalCalibrationUpdate'])->name('verifikasi.periode.kalibrasi.update');
    Route::delete('/verifikasi/periode/kalibrasi/{schedule}', [VerificationPeriodController::class, 'internalCalibrationDestroy'])->name('verifikasi.periode.kalibrasi.destroy');
    Route::get('/verifikasi/periode/kalibrasi/import/form', [VerificationPeriodController::class, 'internalCalibrationImportForm'])->name('verifikasi.periode.kalibrasi.import.form');
    Route::post('/verifikasi/periode/kalibrasi/import', [VerificationPeriodController::class, 'internalCalibrationImport'])->name('verifikasi.periode.kalibrasi.import');
    Route::get('/verifikasi/periode/kalibrasi/export', [VerificationPeriodController::class, 'internalCalibrationExport'])->name('verifikasi.periode.kalibrasi.export');
    Route::get('/verifikasi/periode/kalibrasi/export/pdf', [VerificationPeriodController::class, 'internalCalibrationExportPdf'])->name('verifikasi.periode.kalibrasi.export.pdf');

    // ===== LIST UP PERIODE: VERIFIKASI QC2 =====
    Route::get('/verifikasi/qc2/create', [VerificationPeriodController::class, 'qc2Create'])->name('verifikasi.qc2.create');
    Route::post('/verifikasi/qc2', [VerificationPeriodController::class, 'qc2Store'])->name('verifikasi.qc2.store');
    Route::get('/verifikasi/qc2/{schedule}/edit', [VerificationPeriodController::class, 'qc2Edit'])->name('verifikasi.qc2.edit');
    Route::put('/verifikasi/qc2/{schedule}', [VerificationPeriodController::class, 'qc2Update'])->name('verifikasi.qc2.update');
    Route::delete('/verifikasi/qc2/{schedule}', [VerificationPeriodController::class, 'qc2Destroy'])->name('verifikasi.qc2.destroy');
    Route::get('/verifikasi/qc2/import/form', [VerificationPeriodController::class, 'qc2ImportForm'])->name('verifikasi.qc2.import.form');
    Route::post('/verifikasi/qc2/import', [VerificationPeriodController::class, 'qc2Import'])->name('verifikasi.qc2.import');
    Route::get('/verifikasi/qc2/export/excel', [VerificationPeriodController::class, 'qc2ExportExcel'])->name('verifikasi.qc2.export.excel');
    Route::get('/verifikasi/qc2/export/pdf', [VerificationPeriodController::class, 'qc2ExportPdf'])->name('verifikasi.qc2.export.pdf');

    // ===== KALIBRASI INTERNAL (Jadwal Tahunan) =====
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

    // ===== KALIBRASI EKSTERNAL LIST UP =====
    Route::get('/external-calibration-list-up/import/form', [ExternalCalibrationListUpController::class, 'importForm'])->name('external-list-up.import.form');
    Route::post('/external-calibration-list-up/import', [ExternalCalibrationListUpController::class, 'import'])->name('external-list-up.import');
    Route::get('/external-calibration-list-up/export', [ExternalCalibrationListUpController::class, 'export'])->name('external-list-up.export');
    
    // TAMBAHAN BARU: Route Export PDF List Up
    Route::get('/external-calibration-list-up/export/pdf', [ExternalCalibrationListUpController::class, 'exportPdf'])->name('external-list-up.export.pdf');
    
    Route::get('/external-calibration-list-up/create', [ExternalCalibrationListUpController::class, 'create'])->name('external-list-up.create');
    Route::post('/external-calibration-list-up', [ExternalCalibrationListUpController::class, 'store'])->name('external-list-up.store');
    Route::get('/external-calibration-list-up/{item}/edit', [ExternalCalibrationListUpController::class, 'edit'])->name('external-list-up.edit');
    Route::put('/external-calibration-list-up/{item}', [ExternalCalibrationListUpController::class, 'update'])->name('external-list-up.update');
    Route::delete('/external-calibration-list-up/{item}', [ExternalCalibrationListUpController::class, 'destroy'])->name('external-list-up.destroy');

    // ===== MASTER KALIBRATOR =====
    Route::get('/master-kalibrator/import/form', [MasterCalibratorController::class, 'importForm'])->name('master-calibrator.import.form');
    Route::post('/master-kalibrator/import', [MasterCalibratorController::class, 'import'])->name('master-calibrator.import');
    Route::get('/master-kalibrator/export', [MasterCalibratorController::class, 'export'])->name('master-calibrator.export');
    Route::get('/master-kalibrator/export/pdf', [MasterCalibratorController::class, 'exportPdf'])->name('master-calibrator.export.pdf');
    Route::get('/master-kalibrator/create', [MasterCalibratorController::class, 'create'])->name('master-calibrator.create');
    Route::post('/master-kalibrator', [MasterCalibratorController::class, 'store'])->name('master-calibrator.store');
    Route::get('/master-kalibrator/{masterCalibrator}/edit', [MasterCalibratorController::class, 'edit'])->name('master-calibrator.edit');
    Route::put('/master-kalibrator/{masterCalibrator}', [MasterCalibratorController::class, 'update'])->name('master-calibrator.update');
    Route::delete('/master-kalibrator/{masterCalibrator}', [MasterCalibratorController::class, 'destroy'])->name('master-calibrator.destroy');

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
    
    Route::get('/reports/api/pareto-fitur', [ReportController::class, 'apiParetoFitur'])->name('reports.api.pareto.fitur');
    Route::get('/reports/api/pareto-kategori', [ReportController::class, 'apiParetoKategori'])->name('reports.api.pareto.kategori');
    Route::get('/reports/api/pareto-bulanan', [ReportController::class, 'apiParetoBulanan'])->name('reports.api.pareto.bulanan');
    Route::get('/reports/api/pareto-departemen', [ReportController::class, 'apiParetoDepartemen'])->name('reports.api.pareto.departemen');
    Route::get('/reports/api/chart/{chart}', [ReportController::class, 'apiChart'])->name('reports.api.chart');

    Route::post('/reports/export-all/excel', [ReportController::class, 'exportAllExcel'])->name('reports.export.all.excel');
    Route::post('/reports/export-all/pdf', [ReportController::class, 'exportAllPdf'])->name('reports.export.all.pdf');
    Route::get('/reports/export-log/excel', [ReportController::class, 'exportLogExcel'])->name('reports.export.log.excel');
    Route::get('/reports/export-log/pdf', [ReportController::class, 'exportLogPdf'])->name('reports.export.log.pdf');

    // ===== LAPORAN HARIAN =====
    Route::get('/laporan-harian', [DailyReportController::class, 'index'])->name('laporan.harian.index');
    Route::get('/laporan-harian/preview-nomor', [DailyReportController::class, 'previewNomor'])->name('laporan.harian.preview');
    Route::get('/laporan-harian/export-pdf', [DailyReportController::class, 'exportPdf'])->name('laporan.harian.pdf');
    Route::get('/laporan-harian/export-excel', [DailyReportController::class, 'exportExcel'])->name('laporan.harian.excel');

    // ===== SETTINGS =====
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('/settings/user/create', [SettingController::class, 'createUser'])->name('settings.user.create');
    Route::post('/settings/user', [SettingController::class, 'storeUser'])->name('settings.user.store');
    Route::get('/settings/user/{user}/edit', [SettingController::class, 'editUser'])->name('settings.user.edit');
    Route::put('/settings/user/{user}', [SettingController::class, 'updateUser'])->name('settings.user.update');
    Route::delete('/settings/user/{user}', [SettingController::class, 'destroyUser'])->name('settings.user.destroy');

    // ===== PENGATURAN NOTIFIKASI =====
    Route::get('/settings/notifikasi', [NotificationSettingsController::class, 'notifikasi'])->name('settings.notifikasi');
    Route::post('/settings/notifikasi', [NotificationSettingsController::class, 'updateNotification'])->name('settings.notifikasi.update');
});

require __DIR__.'/auth.php';