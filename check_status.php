<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== WhatsApp Devices ===\n";
$devices = \App\Models\WhatsAppDevice::all();
echo "Total devices: " . count($devices) . "\n";
foreach ($devices as $device) {
    echo "- ID: " . $device->id . ", Name: " . $device->device_name . ", Status: " . $device->status . ", Session: " . ($device->session_id ? "Yes" : "No") . "\n";
}

echo "\n=== Blast Messages (Last 3) ===\n";
$messages = \App\Models\BlastMessage::orderBy('created_at', 'desc')->limit(3)->get();
foreach ($messages as $msg) {
    echo "- ID: " . $msg->id . ", Phone: " . $msg->no_tlp . ", Status: " . $msg->status . ", Sent: " . ($msg->sent_at ? "Yes" : "No") . "\n";
    if ($msg->response) {
        echo "  Response: " . substr($msg->response, 0, 100) . "\n";
    }
}
