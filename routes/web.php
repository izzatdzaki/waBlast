<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardPasienController;
use App\Http\Controllers\DashboardKontrolController;
use App\Http\Controllers\DashboardTindakanController;
use App\Http\Controllers\DashboardMobileBpjsController;
use App\Http\Controllers\AttendanceTrackingController;
use App\Http\Controllers\WhatsAppDashboardController;
use App\Http\Controllers\BirthdayReminderController;

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

    // Tindakan Dashboard
    Route::get('tindakan', [DashboardTindakanController::class, 'index'])->name('tindakan.index');
    Route::get('tindakan/export/laporan', [DashboardTindakanController::class, 'export'])->name('tindakan.export');
    Route::get('tindakan/{no_rawat}/{kd_jenis_prw}/{kd_dokter}/{tgl_perawatan}/{jam_rawat}', [DashboardTindakanController::class, 'show'])->name('tindakan.show');

    // Mobile BPJS Dashboard
    Route::get('mobile-bpjs', [DashboardMobileBpjsController::class, 'index'])->name('mobile_bpjs.index');
    Route::get('mobile-bpjs/{nomorreferensi}', [DashboardMobileBpjsController::class, 'show'])->name('mobile_bpjs.show');

    // Birthday Reminder Dashboard
    Route::get('birthday-reminder', [BirthdayReminderController::class, 'index'])->name('birthday-reminder.index');
    Route::get('birthday-reminder/create', [BirthdayReminderController::class, 'create'])->name('birthday-reminder.create');
    Route::post('birthday-reminder', [BirthdayReminderController::class, 'store'])->name('birthday-reminder.store');
    Route::post('birthday-reminder/{reminder}/send', [BirthdayReminderController::class, 'send'])->name('birthday-reminder.send');
    Route::delete('birthday-reminder/{reminder}', [BirthdayReminderController::class, 'destroy'])->name('birthday-reminder.destroy');
    Route::post('birthday-reminder/sync', [BirthdayReminderController::class, 'sync'])->name('birthday-reminder.sync');
});

// Attendance Tracking
Route::prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceTrackingController::class, 'index'])->name('index');
    Route::get('/export', [AttendanceTrackingController::class, 'export'])->name('export');
});

// WhatsApp Management - No auth required for testing
Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
    Route::get('/', [WhatsAppDashboardController::class, 'index'])->name('dashboard');
    Route::get('send', [WhatsAppDashboardController::class, 'showSendForm'])->name('send');
    Route::post('send', [WhatsAppDashboardController::class, 'sendMessage'])->name('send.post');
    Route::get('schedule', [WhatsAppDashboardController::class, 'showScheduleForm'])->name('schedule');
    Route::post('schedule', [WhatsAppDashboardController::class, 'sendScheduledMessage'])->name('send-scheduled');
    Route::get('history', [WhatsAppDashboardController::class, 'showHistory'])->name('history');
    Route::get('templates', [WhatsAppDashboardController::class, 'showTemplates'])->name('templates');
    Route::get('message/{message}', [WhatsAppDashboardController::class, 'showMessageDetail'])->name('detail');
    Route::post('message/{message}/resend', [WhatsAppDashboardController::class, 'resendMessage'])->name('resend');
    Route::get('settings', [WhatsAppDashboardController::class, 'showSettings'])->name('settings');
    Route::post('settings', [WhatsAppDashboardController::class, 'updateSettings'])->name('settings.update');
    Route::get('devices', [WhatsAppDashboardController::class, 'getDevices'])->name('devices');
    Route::post('devices/qr-code', [WhatsAppDashboardController::class, 'generateQrCode'])->name('qr.generate');
    Route::get('devices/{device_name}/connection-status', [WhatsAppDashboardController::class, 'checkDeviceConnectionStatus'])->name('device.connection-status');
    Route::post('devices/update-status', [WhatsAppDashboardController::class, 'updateDeviceStatus'])->name('device.update-status');
    Route::delete('devices/{device_id}', [WhatsAppDashboardController::class, 'deleteDevice'])->name('device.delete');
    Route::post('webhook/test', [WhatsAppDashboardController::class, 'testWebhook'])->name('webhook.test');
});


