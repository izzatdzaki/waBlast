<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BlastMessage;
use App\Services\WhatsAppService;

echo "=== Mengirim Pesan Pending ===\n";

$pending_messages = BlastMessage::where('status', 'pending')
    ->with('template')
    ->limit(10)
    ->get();

echo "Total pesan pending: " . $pending_messages->count() . "\n\n";

$whatsapp = new WhatsAppService();
$sent = 0;
$failed = 0;

foreach ($pending_messages as $message) {
    try {
        echo "Mengirim ke " . $message->no_tlp . "... ";
        
        $result = $whatsapp->sendMessage(
            $message->no_tlp,
            $message->message
        );

        if (isset($result['success']) && $result['success']) {
            echo "✅ BERHASIL\n";
            $message->status = 'sent';
            $message->external_message_id = $result['message_id'] ?? null;
            $message->response = json_encode($result);
            $message->save();
            $sent++;
        } else {
            echo "❌ GAGAL\n";
            $message->status = 'failed';
            $message->response = json_encode($result);
            $message->save();
            $failed++;
        }
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        $message->status = 'failed';
        $message->response = json_encode(['error' => $e->getMessage()]);
        $message->save();
        $failed++;
    }
}

echo "\n=== HASIL ===\n";
echo "✅ Berhasil: " . $sent . "\n";
echo "❌ Gagal: " . $failed . "\n";
