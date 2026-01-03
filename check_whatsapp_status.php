<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== WhatsApp Devices Status ===\n";
$devices = \DB::table('whatsapp_devices')->get();
echo "Total devices: " . count($devices) . "\n\n";

foreach ($devices as $device) {
    echo "Device ID: " . $device->id . "\n";
    echo "Name: " . $device->device_name . "\n";
    echo "Status: " . $device->status . "\n";
    echo "Session ID: " . ($device->session_id ? substr($device->session_id, 0, 30) . '...' : 'None') . "\n";
    echo "Connected At: " . ($device->connected_at ? $device->connected_at : 'Never') . "\n";
    echo "Updated At: " . $device->updated_at . "\n";
    echo "---\n";
}

echo "\n=== Recent Messages Status ===\n";
$messages = \DB::table('blast_messages')->orderBy('created_at', 'desc')->limit(5)->get();
foreach ($messages as $msg) {
    echo "ID: " . $msg->id . ", Phone: " . $msg->no_tlp . ", Status: " . $msg->status . "\n";
    if ($msg->response) {
        echo "Response: " . substr($msg->response, 0, 80) . "...\n";
    }
}
