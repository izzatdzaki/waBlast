<?php

namespace App\Jobs;

use App\Models\BlastMessage;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $blastMessageId;
    public $tries = 3;
    public $backoff = [10, 60, 300]; // 10s, 60s, 5min

    /**
     * Create a new job instance.
     */
    public function __construct($blastMessageId)
    {
        $this->blastMessageId = $blastMessageId;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService)
    {
        DB::beginTransaction();

        try {
            $blastMessage = BlastMessage::find($this->blastMessageId);

            if (!$blastMessage) {
                Log::error('BlastMessage not found', ['id' => $this->blastMessageId]);
                DB::rollBack();
                return;
            }

            Log::info('Processing WhatsApp message', ['blast_message_id' => $this->blastMessageId]);

            // Get template if exists
            $message = $blastMessage->message;
            if ($blastMessage->template_id) {
                $template = $blastMessage->template;
                if ($template) {
                    // Replace template variables
                    $variables = $blastMessage->template_variables ?? [];
                    $message = $whatsAppService->replaceTemplateVariables($template->isi_pesan, $variables);
                }
            }

            // Format phone number
            $phone = $whatsAppService->formatPhoneNumber($blastMessage->no_tlp);

            // Validate phone number
            if (!$whatsAppService->validatePhoneNumber($phone)) {
                Log::error('Invalid phone number', ['phone' => $phone]);
                $blastMessage->update([
                    'status' => 'failed',
                    'response' => json_encode(['error' => 'Invalid phone number format']),
                    'sent_at' => now(),
                ]);
                DB::commit();
                return;
            }

            // Send message via WhatsApp
            $response = $whatsAppService->sendMessage($phone, $message, [
                'blast_message_id' => $blastMessage->id,
                'sent_from_job' => true,
            ]);

            // Update blast message with success
            $blastMessage->update([
                'status' => 'sent',
                'response' => json_encode($response),
                'sent_at' => now(),
                'external_message_id' => $response['message_id'] ?? null,
            ]);

            Log::info('WhatsApp message sent successfully', [
                'blast_message_id' => $blastMessage->id,
                'message_id' => $response['message_id'] ?? null,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error sending WhatsApp message', [
                'blast_message_id' => $this->blastMessageId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            $blastMessage = BlastMessage::find($this->blastMessageId);
            if ($blastMessage) {
                $blastMessage->update([
                    'status' => $this->attempts() >= $this->tries ? 'failed' : 'pending',
                    'response' => json_encode([
                        'error' => $e->getMessage(),
                        'attempt' => $this->attempts(),
                    ]),
                ]);
            }

            // Fail job on last attempt
            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                // Release back to queue for retry
                $this->release($this->backoff[$this->attempts() - 1] ?? 300);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception)
    {
        Log::error('SendWhatsAppMessage job failed permanently', [
            'blast_message_id' => $this->blastMessageId,
            'error' => $exception->getMessage(),
        ]);

        // Update status to failed
        $blastMessage = BlastMessage::find($this->blastMessageId);
        if ($blastMessage) {
            $blastMessage->update([
                'status' => 'failed',
                'response' => json_encode([
                    'error' => 'Job failed after ' . $this->tries . ' attempts',
                    'exception' => $exception->getMessage(),
                ]),
            ]);
        }
    }
}
