<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendWhatsAppMessageRequest;
use App\Http\Requests\SendScheduledWhatsAppMessageRequest;
use App\Jobs\SendWhatsAppMessage;
use App\Models\BlastMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppMessageController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Send immediate WhatsApp message
     * 
     * POST /api/whatsapp/send
     */
    public function send(SendWhatsAppMessageRequest $request)
    {
        try {
            // Log all input BEFORE validation
            Log::info('Raw request input', ['input' => $request->all()]);
            
            $validated = $request->validated();
            
            Log::info('After validation', ['validated' => $validated]);

            // Ensure phone is set
            if (empty($validated['phone'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor telepon diperlukan',
                    'error' => 'Phone is empty after validation',
                ], 400);
            }

            // Get the device to use
            $device = null;
            if (!empty($validated['device_id'])) {
                $device = \DB::table('whatsapp_devices')
                    ->where('id', $validated['device_id'])
                    ->first();
                
                if (!$device) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Device tidak ditemukan atau tidak aktif',
                        'error' => 'Device ID: ' . $validated['device_id'],
                    ], 400);
                }
            } else {
                // Fallback to first active device
                $device = \DB::table('whatsapp_devices')
                    ->where('status', 'connected')
                    ->first();
                
                if (!$device) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada device WhatsApp yang terhubung. Silakan hubungkan device terlebih dahulu.',
                        'error' => 'No active device found',
                    ], 400);
                }
            }

            // Log data being inserted
            $insertData = [
                'no_tlp' => $validated['phone'],
                'message' => $validated['message'],
                'template_id' => $validated['template_id'] ?? null,
                'template_variables' => $validated['template_variables'] ?? null,
                'device_id' => $device->id,
                'status' => 'pending',
                'tipe_template' => 'immediate',
                'created_by' => auth()->id() ?? 'system',
            ];
            
            Log::info('Data to insert into BlastMessage', ['data' => $insertData]);
            
            // Create blast message record
            $blastMessage = BlastMessage::create($insertData);

            // Send message immediately (sync execution instead of queue)
            try {
                $message = $blastMessage->message;
                if ($blastMessage->template_id) {
                    $template = $blastMessage->template;
                    if ($template) {
                        $variables = $blastMessage->template_variables ?? [];
                        $message = $this->whatsAppService->replaceTemplateVariables($template->isi_pesan, $variables);
                    }
                }

                $phone = $this->whatsAppService->formatPhoneNumber($blastMessage->no_tlp);
                
                if ($this->whatsAppService->validatePhoneNumber($phone)) {
                    $response = $this->whatsAppService->sendMessageWithDevice($phone, $message, $device->device_name, [
                        'blast_message_id' => $blastMessage->id,
                        'sent_from_api' => true,
                        'device_id' => $device->id,
                    ]);

                    $blastMessage->update([
                        'status' => 'sent',
                        'response' => json_encode($response),
                        'sent_at' => now(),
                        'external_message_id' => $response['message_id'] ?? null,
                    ]);
                } else {
                    $blastMessage->update([
                        'status' => 'failed',
                        'response' => json_encode(['error' => 'Format nomor telepon tidak valid']),
                        'sent_at' => now(),
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Format nomor telepon tidak valid',
                        'phone' => $phone,
                    ], 400);
                }
            } catch (\Exception $e) {
                Log::error('Direct send error', ['error' => $e->getMessage()]);
                $blastMessage->update([
                    'status' => 'failed',
                    'response' => json_encode(['error' => $e->getMessage()]),
                    'sent_at' => now(),
                ]);
                
                throw $e;
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim',
                'data' => [
                    'id' => $blastMessage->id,
                    'phone' => $blastMessage->no_tlp,
                    'device' => $device->device_name,
                    'status' => $blastMessage->status,
                    'created_at' => $blastMessage->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('WhatsApp send error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send scheduled WhatsApp message
     * 
     * POST /api/whatsapp/send-scheduled
     */
    public function sendScheduled(SendScheduledWhatsAppMessageRequest $request)
    {
        try {
            $validated = $request->validated();

            // Create blast message record with scheduled status
            $blastMessage = BlastMessage::create([
                'no_tlp' => $validated['phone'],
                'message' => $validated['message'],
                'template_id' => $validated['template_id'] ?? null,
                'template_variables' => $validated['template_variables'] ?? null,
                'status' => 'scheduled',
                'tipe_template' => 'scheduled',
                'scheduled_at' => $validated['scheduled_at'],
                'created_by' => auth()->id() ?? 'system',
            ]);

            // Schedule the job
            SendWhatsAppMessage::dispatch($blastMessage->id)
                ->delay(now()->diffInSeconds($validated['scheduled_at']));

            return response()->json([
                'success' => true,
                'message' => 'Message scheduled successfully',
                'data' => [
                    'id' => $blastMessage->id,
                    'phone' => $blastMessage->no_tlp,
                    'status' => $blastMessage->status,
                    'scheduled_at' => $blastMessage->scheduled_at,
                    'created_at' => $blastMessage->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('WhatsApp schedule error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? 'unknown',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get message history with pagination
     * 
     * GET /api/whatsapp/messages
     */
    public function getHistory(Request $request)
    {
        try {
            $query = BlastMessage::query();

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            // Filter by phone if provided
            if ($request->has('phone')) {
                $query->where('no_tlp', 'LIKE', '%' . $request->input('phone') . '%');
            }

            // Filter by date range if provided
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            $messages = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 50));

            // Calculate statistics
            $allMessages = BlastMessage::query();
            $statistics = [
                'total' => $allMessages->count(),
                'pending' => $allMessages->where('status', 'pending')->count(),
                'sent' => $allMessages->where('status', 'sent')->count(),
                'delivered' => $allMessages->where('status', 'delivered')->count(),
                'failed' => $allMessages->where('status', 'failed')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $messages->items(),
                'statistics' => $statistics,
                'pagination' => [
                    'total' => $messages->total(),
                    'per_page' => $messages->perPage(),
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'from' => $messages->firstItem(),
                    'to' => $messages->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp history error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch message history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single message details
     * 
     * GET /api/whatsapp/messages/{id}
     */
    public function show($id)
    {
        try {
            $message = BlastMessage::find($id);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $message->load('template', 'pasien'),
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp show error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend failed message
     * 
     * PUT /api/whatsapp/messages/{id}/resend
     */
    public function resend($id)
    {
        try {
            $message = BlastMessage::find($id);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found',
                ], 404);
            }

            // Check if message can be resent
            if (!$message->canBeResent()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message cannot be resent. Only failed messages can be resent.',
                ], 400);
            }

            // Reset status and dispatch
            $message->update(['status' => 'pending']);
            SendWhatsAppMessage::dispatch($message->id);

            return response()->json([
                'success' => true,
                'message' => 'Message queued for resending',
                'data' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp resend error', [
                'error' => $e->getMessage(),
                'message_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook for delivery status from Baileys
     * 
     * POST /api/whatsapp/webhook/status
     */
    public function statusCallback(Request $request)
    {
        try {
            $data = $request->validate([
                'message_id' => 'required|string',
                'status' => 'required|in:sent,delivered,read,failed',
                'error' => 'nullable|string',
            ]);

            $message = BlastMessage::where('external_message_id', $data['message_id'])->first();

            if ($message) {
                $message->update([
                    'status' => $data['status'],
                    'response' => json_encode($data),
                    'delivered_at' => in_array($data['status'], ['delivered', 'read']) ? now() : null,
                    'read_at' => $data['status'] === 'read' ? now() : null,
                ]);

                Log::info('WhatsApp status updated', [
                    'message_id' => $data['message_id'],
                    'status' => $data['status'],
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Health check for WhatsApp API
     * 
     * GET /api/whatsapp/health
     */
    public function health()
    {
        try {
            $isHealthy = $this->whatsAppService->healthCheck();

            return response()->json([
                'success' => true,
                'status' => $isHealthy ? 'healthy' : 'unhealthy',
                'message' => $isHealthy ? 'WhatsApp API is running' : 'WhatsApp API is not responding',
            ], $isHealthy ? 200 : 503);
        } catch (\Exception $e) {
            Log::error('WhatsApp health check error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Health check failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
