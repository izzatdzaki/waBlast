<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Devices Terhubung ===\n";
$devices = DB::table('whatsapp_devices')
    ->where('status', 'active')
    ->get();

if ($devices->count() == 0) {
    echo "⚠️ TIDAK ADA DEVICE YANG TERHUBUNG!\n";
} else {
    foreach ($devices as $device) {
        echo "✅ Device ID: " . $device->id . "\n";
        echo "   Device Name: " . $device->device_name . "\n";
        echo "   Phone: " . $device->phone_number . "\n";
        echo "   Status: " . $device->status . "\n";
        echo "   Last Activity: " . $device->last_activity_at . "\n\n";
    }
}

echo "=== Pesan yang Pending ===\n";
$pending = DB::table('blast_messages')
    ->where('status', 'pending')
    ->limit(5)
    ->get();

echo "Total pesan pending: " . $pending->count() . "\n";
foreach ($pending as $msg) {
    echo "- " . $msg->no_tlp . " (ID: " . $msg->id . ")\n";
}

echo "\n=== Backend Connection Status ===\n";
$ch = curl_init('http://localhost:3000/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "✅ Backend API Running\n";
} else {
    echo "❌ Backend API Not Responding (HTTP " . $http_code . ")\n";
}
