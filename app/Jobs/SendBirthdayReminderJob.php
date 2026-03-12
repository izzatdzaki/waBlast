<?php

namespace App\Jobs;

use App\Models\BirthdayReminder;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBirthdayReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reminder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BirthdayReminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(WhatsAppService $whatsappService)
    {
        try {
            // Skip jika sudah dikirim
            if ($this->reminder->status === 'sent') {
                Log::info("Birthday reminder already sent", [
                    'reminder_id' => $this->reminder->id
                ]);
                return;
            }

            // Kirim pesan
            $response = $whatsappService->sendMessage(
                $this->reminder->receiver_phone,
                $this->reminder->message
            );

            // Update status
            $this->reminder->update([
                'status' => 'sent',
                'response' => json_encode($response),
                'sent_at' => now(),
            ]);

            Log::info("Birthday reminder sent successfully", [
                'reminder_id' => $this->reminder->id,
                'patient' => $this->reminder->no_rkm_medis,
                'receiver' => $this->reminder->receiver_phone,
            ]);
        } catch (\Exception $e) {
            // Update status menjadi failed
            $this->reminder->update([
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()])
            ]);

            Log::error("Birthday reminder failed to send", [
                'reminder_id' => $this->reminder->id,
                'error' => $e->getMessage()
            ]);

            // Retry job
            throw $e;
        }
    }
}
