<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Pesan Status Gagal ===\n";
$failed = DB::table('blast_messages')
    ->where('status', 'failed')
    ->select('id', 'no_tlp', 'message', 'status', 'response', 'created_at')
    ->get();

foreach ($failed as $msg) {
    echo "\nID: " . $msg->id . "\n";
    echo "Phone: " . $msg->no_tlp . "\n";
    echo "Status: " . $msg->status . "\n";
    echo "Error: " . $msg->response . "\n";
    echo "---\n";
}

echo "\n=== Pesan Status Menunggu ===\n";
$pending = DB::table('blast_messages')
    ->where('status', 'pending')
    ->select('id', 'no_tlp', 'message', 'status', 'created_at')
    ->get();

echo "Total: " . $pending->count() . " pesan\n";
foreach ($pending as $msg) {
    echo "- ID " . $msg->id . ": " . $msg->no_tlp . "\n";
}

echo "\n=== Solusi ===\n";
echo "Sekarang akan reset semua pesan gagal dan menunggu ke 'pending'\n";
echo "Kemudian coba kirim ulang...\n";

// Reset semua failed dan pending messages
DB::table('blast_messages')
    ->whereIn('status', ['failed', 'pending'])
    ->update([
        'status' => 'pending',
        'response' => null,
        'external_message_id' => null,
    ]);

echo "\n✅ Semua pesan di-reset ke 'pending'. Sekarang akan diproses ulang.\n";
