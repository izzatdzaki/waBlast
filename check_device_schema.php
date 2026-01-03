<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== WhatsApp Devices Table Schema ===\n";
$columns = DB::select("SELECT COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'whatsapp_devices'", [env('DB_DATABASE')]);

foreach ($columns as $col) {
    echo $col->COLUMN_NAME . " - " . $col->COLUMN_TYPE . "\n";
}

echo "\n=== How to Add Device ===\n";
echo "1. Go to: http://127.0.0.1:8000/whatsapp/devices\n";
echo "2. Click 'Generate QR Code'\n";
echo "3. Scan with WhatsApp from your phone\n";
echo "4. Wait for status to change to 'connected'\n";
