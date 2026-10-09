<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\VerifikasiController;
use App\Http\Controllers\InternalController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;

/*
|--------------------------------------------------------------------------
| ROUTE PUBLIK (Guest / Belum Login)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Halaman Guest (View Only) - Bisa diakses tanpa login
Route::get('/view', [GuestController::class, 'index'])->name('guest.index');
Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
Route::get('/internal', [InternalController::class, 'index'])->name('internal.index');
Route::get('/eksternal-small', [CalibrationController::class, 'eksternalSmall'])->name('eksternal.small');
Route::get('/mesin', [CalibrationController::class, 'mesin'])->name('mesin.index');

/*
|--------------------------------------------------------------------------
| ROUTE USER (Harus Login - Hanya View)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard untuk User (View Only)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});

/*
|--------------------------------------------------------------------------
| ROUTE ADMIN (Harus Login & Role = admin - Akses Penuh)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Daftar Induk - Aksi
    Route::get('/tools/create', [ToolController::class, 'create'])->name('tools.create');
    Route::post('/tools', [ToolController::class, 'store'])->name('tools.store');
    Route::get('/tools/{tool}/edit', [ToolController::class, 'edit'])->name('tools.edit');
    Route::put('/tools/{tool}', [ToolController::class, 'update'])->name('tools.update');
    Route::delete('/tools/{tool}', [ToolController::class, 'destroy'])->name('tools.destroy');

    // Import Excel
    Route::get('/tools/import/form', [ToolController::class, 'importForm'])->name('tools.import.form');
    Route::post('/tools/import', [ToolController::class, 'importStore'])->name('tools.import.store');

    // Jadwal Verifikasi - Aksi
    Route::get('/verifikasi/create', [VerifikasiController::class, 'create'])->name('verifikasi.create');
    Route::post('/verifikasi', [VerifikasiController::class, 'store'])->name('verifikasi.store');
    Route::get('/verifikasi/{verifikasi}/edit', [VerifikasiController::class, 'edit'])->name('verifikasi.edit');
    Route::put('/verifikasi/{verifikasi}', [VerifikasiController::class, 'update'])->name('verifikasi.update');
    Route::delete('/verifikasi/{verifikasi}', [VerifikasiController::class, 'destroy'])->name('verifikasi.destroy');

    // Kalibrasi Internal - Aksi
    Route::get('/internal/create', [InternalController::class, 'create'])->name('internal.create');
    Route::post('/internal', [InternalController::class, 'store'])->name('internal.store');
    Route::get('/internal/{internal}/edit', [InternalController::class, 'edit'])->name('internal.edit');
    Route::put('/internal/{internal}', [InternalController::class, 'update'])->name('internal.update');
    Route::delete('/internal/{internal}', [InternalController::class, 'destroy'])->name('internal.destroy');

    // Kalibrasi Eksternal Small - Aksi
    Route::get('/eksternal-small/create', [CalibrationController::class, 'eksternalSmallCreate'])->name('eksternal.small.create');
    Route::post('/eksternal-small', [CalibrationController::class, 'eksternalSmallStore'])->name('eksternal.small.store');
    Route::get('/eksternal-small/{eksternal}/edit', [CalibrationController::class, 'eksternalSmallEdit'])->name('eksternal.small.edit');
    Route::put('/eksternal-small/{eksternal}', [CalibrationController::class, 'eksternalSmallUpdate'])->name('eksternal.small.update');
    Route::delete('/eksternal-small/{eksternal}', [CalibrationController::class, 'eksternalSmallDestroy'])->name('eksternal.small.destroy');

    // Kalibrasi Eksternal Mesin - Aksi
    Route::get('/mesin/create', [CalibrationController::class, 'mesinCreate'])->name('mesin.create');
    Route::post('/mesin', [CalibrationController::class, 'mesinStore'])->name('mesin.store');
    Route::get('/mesin/{mesin}/edit', [CalibrationController::class, 'mesinEdit'])->name('mesin.edit');
    Route::put('/mesin/{mesin}', [CalibrationController::class, 'mesinUpdate'])->name('mesin.update');
    Route::delete('/mesin/{mesin}', [CalibrationController::class, 'mesinDestroy'])->name('mesin.destroy');
});

require __DIR__.'/auth.php';