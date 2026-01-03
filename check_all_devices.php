<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Semua Devices di Database ===\n";
$all_devices = DB::table('whatsapp_devices')->get();

foreach ($all_devices as $device) {
    echo "Device ID: " . $device->id . "\n";
    echo "Device Name: " . $device->device_name . "\n";
    echo "Phone: " . $device->phone_number . "\n";
    echo "Status: " . $device->status . " ← Ini status di database\n";
    echo "Last Connected: " . $device->last_connected_at . "\n";
    echo "---\n";
}

echo "\nNOTA: Status harus 'active' agar pesan bisa terkirim.\n";
echo "Di screenshot terlihat 'Terhubung' tapi di database bisa jadi statusnya 'connecting' atau 'inactive'.\n";
