<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Status Semua Pesan ===\n";
$messages = DB::table('blast_messages')->get();

$summary = [];
foreach ($messages as $msg) {
    if (!isset($summary[$msg->status])) {
        $summary[$msg->status] = 0;
    }
    $summary[$msg->status]++;
}

foreach ($summary as $status => $count) {
    echo "$status: $count\n";
}

echo "\n=== Detail Pesan ===\n";
foreach ($messages as $msg) {
    echo "ID " . $msg->id . " | " . $msg->no_tlp . " | " . $msg->status . "\n";
}
