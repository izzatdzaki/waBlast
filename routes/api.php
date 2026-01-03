<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppMessageController;
use App\Http\Controllers\WhatsAppDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * WhatsApp Messaging Routes
 */
Route::prefix('whatsapp')->name('whatsapp.api.')->group(function () {
    // Health check (no auth required)
    Route::get('/health', [WhatsAppMessageController::class, 'health'])->name('health');
    
    // Backend health check
    Route::get('/backend-health', [WhatsAppDashboardController::class, 'checkBackendHealth'])->name('backend-health');

    // Webhook for delivery status (no auth required)
    Route::post('/webhook/status', [WhatsAppMessageController::class, 'statusCallback'])->name('webhook.status');

    // Webhook for device status updates from backend (no auth required)
    Route::post('/webhook/device', [WhatsAppDashboardController::class, 'handleDeviceWebhook'])->name('webhook.device');

    // Routes for web UI - no auth required for testing
    Route::post('/send', [WhatsAppMessageController::class, 'send'])->name('send');
    Route::post('/send-scheduled', [WhatsAppMessageController::class, 'sendScheduled'])->name('send-scheduled');
    Route::get('/messages', [WhatsAppMessageController::class, 'getHistory'])->name('history');
    Route::get('/messages/{id}', [WhatsAppMessageController::class, 'show'])->name('show');
    Route::put('/messages/{id}/resend', [WhatsAppMessageController::class, 'resend'])->name('resend');
    
    // Device routes
    Route::get('/devices', [WhatsAppDashboardController::class, 'getDevices'])->name('devices');
    Route::post('/devices/{id}/delete', [WhatsAppDashboardController::class, 'deleteDevice'])->name('device.delete');
    
    // Template routes (add placeholder controller methods if needed)
    Route::post('/templates', function() { return response()->json(['error' => 'Not implemented'], 501); })->name('template.create');
    Route::get('/templates/{id}', function() { return response()->json(['error' => 'Not implemented'], 501); })->name('template.show');
    Route::put('/templates/{id}', function() { return response()->json(['error' => 'Not implemented'], 501); })->name('template.update');
    Route::delete('/templates/{id}', function() { return response()->json(['error' => 'Not implemented'], 501); })->name('template.delete');
});

