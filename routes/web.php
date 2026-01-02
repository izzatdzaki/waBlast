<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardPasienController;
use App\Http\Controllers\DashboardKontrolController;
use App\Http\Controllers\DashboardMobileBpjsController;
use App\Http\Controllers\AttendanceTrackingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.main');

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    // Pasien Dashboard
    Route::get('pasien', [DashboardPasienController::class, 'index'])->name('pasien.index');
    Route::get('pasien/{no_rkm_medis}', [DashboardPasienController::class, 'show'])->name('pasien.show');

    // Kontrol Dashboard
    Route::get('kontrol', [DashboardKontrolController::class, 'index'])->name('kontrol.index');
    Route::get('kontrol/{no_surat}', [DashboardKontrolController::class, 'show'])->name('kontrol.show');
    Route::post('kontrol/{no_surat}/send-reminder', [DashboardKontrolController::class, 'sendReminder'])->name('kontrol.sendReminder');

    // Mobile BPJS Dashboard
    Route::get('mobile-bpjs', [DashboardMobileBpjsController::class, 'index'])->name('mobile_bpjs.index');
    Route::get('mobile-bpjs/{nomorreferensi}', [DashboardMobileBpjsController::class, 'show'])->name('mobile_bpjs.show');
});

// Attendance Tracking
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceTrackingController::class, 'index'])->name('index');
    Route::get('/export', [AttendanceTrackingController::class, 'export'])->name('export');
});

