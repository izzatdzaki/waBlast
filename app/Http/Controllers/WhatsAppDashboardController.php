<?php

namespace App\Http\Controllers;

use App\Models\BlastMessage;
use App\Models\BlastTemplate;
use App\Models\Pasien;
use App\Models\WhatsAppSettings;
use App\Models\WhatsAppDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsAppDashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $stats = [
            'total_sent' => BlastMessage::where('status', 'sent')->count(),
            'total_pending' => BlastMessage::where('status', 'pending')->count(),
            'total_failed' => BlastMessage::where('status', 'failed')->count(),
            'total_delivered' => BlastMessage::where('status', 'delivered')->count(),
        ];

        $recent_messages = BlastMessage::select('*')
            ->with('template', 'pasien')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $templates = BlastTemplate::where('is_active', true)->get();

        return view('whatsapp.dashboard', compact('stats', 'recent_messages', 'templates'));
    }

    /**
     * Show send message form
     */
    public function showSendForm()
    {
        $templates = BlastTemplate::where('is_active', true)->get();
        $patients = Pasien::whereNotNull('no_tlp')
            ->select('no_rkm_medis', 'nm_pasien', 'no_tlp')
            ->limit(100)
            ->get();
        $devices = WhatsAppDevice::where('status', 'active')
            ->get(['id', 'device_name', 'phone_number', 'status']);

        return view('whatsapp.send-message', compact('templates', 'patients', 'devices'));
    }

    /**
     * Show schedule message form
     */
    public function showScheduleForm()
    {
        $templates = BlastTemplate::where('is_active', true)->get();
        $patients = Pasien::whereNotNull('no_tlp')
            ->select('no_rkm_medis', 'nm_pasien', 'no_tlp')
            ->limit(100)
            ->get();

        return view('whatsapp.schedule-message', compact('templates', 'patients'));
    }

    /**
     * Show message history
     */
    public function showHistory(Request $request)
    {
        $query = BlastMessage::query();

        // Filter by status - using input() to ensure proper handling
        $status = $request->input('status', '');
        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by date range (note: JavaScript sends from_date and to_date)
        $fromDate = $request->input('from_date');
        if (!empty($fromDate)) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        
        $toDate = $request->input('to_date');
        if (!empty($toDate)) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        // Search
        $search = $request->input('search', '');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('no_telp', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('pesan', 'like', "%{$search}%");
            });
        }

        // Check count before pagination
        $countBeforePaginate = $query->count();
        
        $messages = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Load relations for returned data
        $messages->load('template', 'pasien');

        // Return JSON if Accept header requests it
        if ($request->wantsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'data' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ],
                'debug' => [
                    'total_in_db' => BlastMessage::count(),
                    'count_after_filters' => $countBeforePaginate,
                    'items_returned' => count($messages->items()),
                    'filters_applied' => [
                        'status' => $request->status ?? 'none',
                        'from_date' => $request->from_date ?? 'none',
                        'to_date' => $request->to_date ?? 'none',
                        'search' => $request->search ?? 'none',
                    ],
                    'raw_query' => $query->toSql(),
                ]
            ]);
        }

        return view('whatsapp.message-history', compact('messages'));
    }

    /**
     * Show templates management
     */
    public function showTemplates()
    {
        $templates = BlastTemplate::orderBy('created_at', 'desc')->paginate(10);

        return view('whatsapp.templates', compact('templates'));
    }

    /**
     * Store new template
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:255',
            'isi_pesan' => 'required|string|max:4096',
            'category' => 'nullable|string|max:100',
        ]);

        $template = new BlastTemplate($validated);
        $template->is_active = true;

        // Extract placeholders
        preg_match_all('/\{([^}]+)\}/', $validated['isi_pesan'], $matches);
        $template->placeholder_variables = $matches[1] ?? [];

        $template->save();

        return redirect()->route('whatsapp.templates')
            ->with('success', 'Template berhasil dibuat!');
    }

    /**
     * Update template
     */
    public function updateTemplate(Request $request, BlastTemplate $template)
    {
        $validated = $request->validate([
            'nama_template' => 'required|string|max:255',
            'isi_pesan' => 'required|string|max:4096',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        // Extract placeholders
        preg_match_all('/\{([^}]+)\}/', $validated['isi_pesan'], $matches);
        $template->placeholder_variables = $matches[1] ?? [];
        $template->save();

        return redirect()->route('whatsapp.templates')
            ->with('success', 'Template berhasil diperbarui!');
    }

    /**
     * Delete template
     */
    public function deleteTemplate(BlastTemplate $template)
    {
        $template->delete();

        return redirect()->route('whatsapp.templates')
            ->with('success', 'Template berhasil dihapus!');
    }

    /**
     * Show single message detail
     */
    public function showMessageDetail(BlastMessage $message)
    {
        $message->load('template', 'pasien');

        // Return JSON if requested via Accept header
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $message
            ]);
        }

        return view('whatsapp.message-detail', compact('message'));
    }

    /**
     * Resend message
     */
    public function resendMessage(BlastMessage $message)
    {
        if (!$message->canBeResent()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pesan tidak dapat dikirim ulang!'], 400);
            }
            return redirect()->back()->with('error', 'Pesan tidak dapat dikirim ulang!');
        }

        try {
            // Send message directly (synchronously) without queue
            $whatsAppService = app(\App\Services\WhatsAppService::class);
            
            // Get template if exists
            $message_text = $message->message;
            if ($message->template_id) {
                $template = $message->template;
                if ($template) {
                    // Replace template variables
                    $variables = $message->template_variables ?? [];
                    $message_text = $whatsAppService->replaceTemplateVariables($template->isi_pesan, $variables);
                }
            }

            // Format phone number
            $phone = $whatsAppService->formatPhoneNumber($message->no_tlp);

            // Validate phone number
            if (!$whatsAppService->validatePhoneNumber($phone)) {
                $message->update([
                    'status' => 'failed',
                    'response' => json_encode(['error' => 'Invalid phone number format']),
                    'sent_at' => now(),
                ]);
                throw new \Exception('Invalid phone number format');
            }

            // Send message via WhatsApp - directly, not via job
            $response = $whatsAppService->sendMessage($phone, $message_text, [
                'blast_message_id' => $message->id,
                'sent_from_resend' => true,
            ]);

            // Update message with success
            $message->update([
                'status' => 'sent',
                'response' => json_encode($response),
                'sent_at' => now(),
                'external_message_id' => $response['message_id'] ?? null,
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim ulang',
                    'status' => $message->status,
                    'data' => $message
                ]);
            }

            return redirect()->back()->with('success', 'Pesan berhasil dikirim ulang!');
        } catch (\Exception $e) {
            // Update message as failed
            $message->update([
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()]),
                'sent_at' => now(),
            ]);

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal mengirim ulang pesan: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengirim ulang pesan: ' . $e->getMessage());
        }
    }

    /**
     * Get template preview
     */
    public function getTemplatePreview(Request $request)
    {
        $template = BlastTemplate::find($request->template_id);

        if (!$template) {
            return response()->json(['error' => 'Template tidak ditemukan'], 404);
        }

        $variables = $request->variables ?? [];
        $preview = $template->getPreview($variables);

        return response()->json([
            'success' => true,
            'preview' => $preview,
        ]);
    }

    /**
     * Show settings page
     */
    public function showSettings()
    {
        $settings = WhatsAppSettings::getSettings();
        
        // Ensure baileys_url is valid, fallback to default if empty
        $baileysUrl = $settings->baileys_url ?: config('services.baileys.api_url', 'http://localhost:3000');
        
        return view('whatsapp.settings', [
            'baileys_url' => $baileysUrl,
            'baileys_status' => $settings->baileys_status,
            'webhook_url' => $settings->webhook_url,
            'webhook_enabled' => $settings->webhook_enabled,
            'webhook_secret' => $settings->webhook_secret,
            'enable_auto_reply' => $settings->enable_auto_reply,
            'auto_reply_message' => $settings->auto_reply_message,
            'message_retention_days' => $settings->message_retention_days,
            'max_message_length' => $settings->max_message_length,
            'api_rate_limit' => $settings->api_rate_limit,
            'api_timeout' => $settings->api_timeout,
            'api_retry_attempts' => $settings->api_retry_attempts,
            'api_retry_delay' => $settings->api_retry_delay,
            'device_check_interval' => $settings->device_check_interval,
            'default_device_id' => $settings->default_device_id,
        ]);
    }

    /**
     * Check backend health status
     */
    public function checkBackendHealth()
    {
        try {
            $settings = WhatsAppSettings::getSettings();
            $baileysUrl = $settings->baileys_url ?? config('services.baileys.api_url', 'http://localhost:3000');
            
            $response = Http::timeout(5)->get($baileysUrl . '/health');
            
            if ($response->status() === 200) {
                WhatsAppSettings::updateSetting('baileys_status', true);
                return response()->json([
                    'success' => true,
                    'status' => 'online',
                    'message' => 'Baileys backend berjalan dengan baik',
                    'url' => $baileysUrl,
                    'timestamp' => now()->toDateTimeString(),
                ]);
            }
        } catch (\Exception $e) {
            WhatsAppSettings::updateSetting('baileys_status', false);
            
            return response()->json([
                'success' => false,
                'status' => 'offline',
                'message' => 'Baileys backend tidak dapat dijangkau',
                'error' => $e->getMessage(),
                'suggestions' => [
                    'Pastikan backend Node.js berjalan: npm start di folder backend/',
                    'Cek apakah port 3000 tidak tertutup firewall',
                    'Lihat console backend untuk error messages',
                ],
            ], 503);
        }
    }

    /**
     * Check Baileys backend status
     */
    private function checkBaileysStatus()
    {
        try {
            $settings = WhatsAppSettings::getSettings();
            $url = $settings->baileys_url ?? config('services.baileys.url', 'http://localhost:3000');
            
            $response = Http::timeout(5)->get($url . '/health');
            
            // Update status in database
            if ($response->status() === 200) {
                WhatsAppSettings::updateSetting('baileys_status', true);
                return true;
            }
        } catch (\Exception $e) {
            // Update status in database
            WhatsAppSettings::updateSetting('baileys_status', false);
            \Log::warning('Baileys health check failed', ['error' => $e->getMessage()]);
        }
        
        return false;
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            // Connection settings
            'baileys_url' => 'required|url',
            
            // Device settings
            'device_check_interval' => 'required|integer|min:10|max:300',
            'default_device_id' => 'nullable|string|max:255',
            
            // Webhook settings
            'webhook_url' => 'nullable|url',
            'webhook_enabled' => 'boolean',
            'webhook_secret' => 'nullable|string|max:255',
            
            // Message settings
            'enable_auto_reply' => 'boolean',
            'auto_reply_message' => 'nullable|string|max:4096',
            'message_retention_days' => 'required|integer|min:1|max:365',
            'max_message_length' => 'required|integer|min:100|max:4096',
            
            // API settings
            'api_rate_limit' => 'required|integer|min:1|max:100',
            'api_timeout' => 'required|integer|min:5|max:300',
            'api_retry_attempts' => 'required|integer|min:1|max:10',
            'api_retry_delay' => 'required|integer|min:1|max:60',
        ]);

        // Prepare data for database
        $settingsData = [
            'baileys_url' => $validated['baileys_url'],
            'device_check_interval' => $validated['device_check_interval'],
            'default_device_id' => $validated['default_device_id'] ?? null,
            'webhook_url' => $validated['webhook_url'] ?? null,
            'webhook_enabled' => $request->has('webhook_enabled'),
            'webhook_secret' => $validated['webhook_secret'] ?? null,
            'enable_auto_reply' => $request->has('enable_auto_reply'),
            'auto_reply_message' => $validated['auto_reply_message'] ?? null,
            'message_retention_days' => $validated['message_retention_days'],
            'max_message_length' => $validated['max_message_length'],
            'api_rate_limit' => $validated['api_rate_limit'],
            'api_timeout' => $validated['api_timeout'],
            'api_retry_attempts' => $validated['api_retry_attempts'],
            'api_retry_delay' => $validated['api_retry_delay'],
        ];

        // Save to database
        WhatsAppSettings::updateSettings($settingsData);

        return redirect()->route('whatsapp.settings')
            ->with('success', 'Seluruh pengaturan WhatsApp berhasil disimpan ke database!');
    }

    /**
     * Get Baileys devices/sessions - untuk settings page tampilkan semua
     */
    public function getDevices()
    {
        try {
            // Return ALL devices (active, connecting, disconnected) untuk settings page
            $devices = WhatsAppDevice::select('id', 'device_name', 'phone_number', 'status')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'device_id' => $device->id,
                        'device_name' => $device->device_name,
                        'phone_number' => $device->phone_number,
                        'phone' => $device->phone_number,
                        'status' => $device->status,
                        'status_label' => $device->getStatusLabel(),
                        'connected' => $device->status === 'active',
                        'ready' => $device->status === 'active',
                    ];
                });

            \Log::info('Get Devices - Total: ' . $devices->count());

            return response()->json([
                'success' => true,
                'devices' => $devices,
                'data' => $devices, // Alternate key for compatibility
                'total' => $devices->count(),
                'ready_count' => $devices->where('ready', true)->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Devices Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'devices' => [],
                'data' => [],
            ], 500);
        }
    }
    
    /**
     * Get active devices only - untuk send message (private use)
     */
    public function getActiveDevices()
    {
        try {
            $devices = WhatsAppDevice::where('status', 'active')
                ->select('id', 'device_name', 'phone_number', 'status')
                ->get()
                ->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'device_name' => $device->device_name,
                        'phone_number' => $device->phone_number,
                        'status' => $device->status,
                        'ready' => true,
                    ];
                });

            return response()->json([
                'success' => true,
                'devices' => $devices,
                'ready_count' => $devices->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Active Devices Error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'devices' => [],
            ], 500);
        }
    }

    /**
     * Generate QR code for WhatsApp pairing
     */
    public function generateQrCode(Request $request)
    {
        try {
            // Get Baileys URL from database settings
            $settings = WhatsAppSettings::getSettings();
            $baileysUrl = $settings->baileys_url ?? config('services.baileys.url', 'http://localhost:3000');
            $deviceId = 'device_' . uniqid();
            
            \Log::info('QR Code Generation Started', [
                'baileys_url' => $baileysUrl,
                'device_id' => $deviceId,
            ]);

            // Step 1: Create new session
            $response = Http::timeout(15)->post(
                $baileysUrl . '/sessions/new',
                ['device_id' => $deviceId]
            );

            $data = $response->json();
            
            if (!isset($data['device_id'])) {
                \Log::error('Session creation failed', $data);
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal membuat session baru di Baileys backend',
                    'details' => $data,
                ], 500);
            }

            $sessionId = $data['device_id'];
            
            // Step 2: Poll for QR code (with retry logic)
            $maxAttempts = 8;
            $attempt = 0;
            $qrCode = null;

            while ($attempt < $maxAttempts && !$qrCode) {
                sleep(1); // Wait 1 second before checking
                
                try {
                    $qrResponse = Http::timeout(10)->get(
                        $baileysUrl . '/qr?sessionId=' . $sessionId
                    );
                    
                    $qrData = $qrResponse->json();
                    
                    \Log::info('QR Code Poll Attempt ' . ($attempt + 1), [
                        'session_id' => $sessionId,
                        'response' => $qrData,
                    ]);

                    if (isset($qrData['qr'])) {
                        $qrCode = $qrData['qr'];
                        break;
                    }

                    // Check if already connected
                    if (isset($qrData['connected']) && $qrData['connected']) {
                        return response()->json([
                            'success' => true,
                            'data' => [
                                'already_connected' => true,
                                'phone' => $qrData['phone'] ?? 'Unknown',
                                'device_id' => $sessionId,
                            ],
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('QR Poll Error on attempt ' . ($attempt + 1), [
                        'error' => $e->getMessage(),
                    ]);
                }

                $attempt++;
            }

            if (!$qrCode) {
                \Log::error('QR Code generation timeout', [
                    'session_id' => $sessionId,
                    'attempts' => $maxAttempts,
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'QR Code tidak bisa dibuat. Pastikan Baileys backend berjalan dengan baik di ' . $baileysUrl,
                    'suggestions' => [
                        '1. Pastikan backend Node.js berjalan: npm start di folder backend/',
                        '2. Cek apakah port 3000 tidak tertutup firewall',
                        '3. Lihat console backend untuk error messages',
                        '4. Coba refresh halaman dan cek kembali',
                    ],
                ], 500);
            }
            
            // STEP 3: Create device record in database with status 'connecting'
            $deviceName = $request->input('device_name', 'Default Device');
            $device = WhatsAppDevice::firstOrCreate(
                ['device_name' => $sessionId],
                [
                    'device_id' => $sessionId,
                    'device_name' => $deviceName,
                    'status' => 'connecting',
                    'phone_number' => '',  // Empty string, will be filled after QR scan
                ]
            );
            
            \Log::info('Device created for QR scanning', [
                'session_id' => $sessionId,
                'device_id' => $device->id,
                'device_name' => $deviceName,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'qr' => $qrCode,
                    'device_id' => $sessionId,
                    'db_device_id' => $device->id,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('QR Code Generation Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'suggestions' => [
                    'Pastikan Baileys backend sedang berjalan',
                    'Cek file .env untuk BAILEYS_URL yang benar',
                    'Lihat logs: storage/logs/laravel.log',
                ],
            ], 500);
        }
    }

    /**
     * Delete device session
     */
    public function deleteDevice($device_id)
    {
        try {
            // Find device by ID or device_name
            $device = WhatsAppDevice::where('id', $device_id)
                ->orWhere('device_name', $device_id)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error' => 'Device tidak ditemukan',
                ], 404);
            }

            $baileysUrl = config('services.baileys.url', 'http://localhost:3000');
            
            // Try to delete from backend (Baileys)
            try {
                Http::timeout(5)->delete($baileysUrl . '/sessions/' . $device->device_name);
            } catch (\Exception $e) {
                // Log but don't fail if backend delete fails
                \Log::warning('Failed to delete session from Baileys', [
                    'device_name' => $device->device_name,
                    'error' => $e->getMessage(),
                ]);
            }

            // Delete from database
            $device->delete();

            \Log::info('Device deleted successfully', [
                'device_id' => $device->id,
                'device_name' => $device->device_name,
                'phone' => $device->phone_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            \Log::error('Delete device error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check device connection status from Baileys backend
     */
    public function checkDeviceConnectionStatus($device_name)
    {
        try {
            $settings = WhatsAppSettings::getSettings();
            $baileysUrl = $settings->baileys_url ?? config('services.baileys.api_url', 'http://localhost:3000');
            
            // Check status via Baileys connection-status endpoint
            $response = Http::timeout(10)->get($baileysUrl . '/connection-status/' . $device_name);
            
            $data = $response->json();
            
            \Log::info('Device connection status check', [
                'device_name' => $device_name,
                'baileys_response' => $data,
            ]);
            
            // If device is authenticated and ready
            if ($data['authenticated'] ?? false && $data['ready'] ?? false) {
                // Update device status in database
                $phone = $data['phone'] ?? $data['phoneNumber'] ?? '';
                
                WhatsAppDevice::where('device_name', $device_name)->update([
                    'status' => 'active',
                    'phone_number' => $phone ?: '',  // Fill phone number if available
                    'last_connected_at' => now(),
                ]);
                
                \Log::info('Device marked as active', [
                    'device_name' => $device_name,
                    'phone' => $phone,
                ]);
                
                return response()->json([
                    'success' => true,
                    'authenticated' => true,
                    'ready' => true,
                    'phone' => $phone,
                    'status' => 'active',
                    'message' => 'Device terhubung',
                ]);
            } else if ($data['authenticated'] ?? false) {
                return response()->json([
                    'success' => true,
                    'authenticated' => true,
                    'ready' => false,
                    'status' => $data['status'] ?? 'connecting',
                    'message' => 'Device sedang siap (belum fully ready)',
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'authenticated' => false,
                    'ready' => false,
                    'status' => 'connecting',
                    'message' => 'Device belum ter-authenticate',
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Check device connection error', [
                'device_name' => $device_name,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'ready' => false,
                'error' => $e->getMessage(),
                'message' => 'Gagal check status device',
            ], 500);
        }
    }

    /**
     * Webhook for device status updates from backend
     */
    public function handleDeviceWebhook(Request $request)
    {
        try {
            $event = $request->input('event');
            $device_id = $request->input('device_id');
            $data = $request->input('data', []);

            \Log::info('Device webhook received', [
                'event' => $event,
                'device_id' => $device_id,
                'data' => $data,
            ]);

            if ($event === 'device_ready') {
                // Device is ready/connected - VERIFY before marking active
                $device = \DB::table('whatsapp_devices')
                    ->where('device_name', $device_id)
                    ->first();

                if (!$device) {
                    \DB::table('whatsapp_devices')->insert([
                        'device_name' => $device_id,
                        'phone_number' => $data['phone_number'] ?? '',
                        'status' => 'active', // Webhook says device is truly authenticated
                        'device_info' => json_encode($data),
                        'last_connected_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    \Log::info('Device created and marked active', ['device_id' => $device_id]);
                } else {
                    \DB::table('whatsapp_devices')
                        ->where('device_name', $device_id)
                        ->update([
                            'status' => 'active', // Only mark active when webhook confirms authenticated
                            'phone_number' => $data['phone_number'] ?? $device->phone_number,
                            'device_info' => json_encode($data),
                            'last_connected_at' => now(),
                            'updated_at' => now(),
                        ]);
                    \Log::info('Device updated to active', ['device_id' => $device_id]);
                }
            } elseif ($event === 'qr') {
                // QR generated but not yet authenticated - mark as connecting
                // CREATE device if doesn't exist yet
                $device = \DB::table('whatsapp_devices')
                    ->where('device_name', $device_id)
                    ->first();

                if (!$device) {
                    \DB::table('whatsapp_devices')->insert([
                        'device_name' => $device_id,
                        'phone_number' => '',  // Empty string, will be filled after QR scan
                        'status' => 'connecting',
                        'device_info' => json_encode($data),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    \Log::info('Device created (QR pending)', ['device_id' => $device_id]);
                } else {
                    \DB::table('whatsapp_devices')
                        ->where('device_name', $device_id)
                        ->update([
                            'status' => 'connecting',
                            'updated_at' => now(),
                        ]);
                    \Log::info('Device marked connecting (QR pending)', ['device_id' => $device_id]);
                }
            } elseif ($event === 'connection_closed') {
                // Device disconnected
                \DB::table('whatsapp_devices')
                    ->where('device_name', $device_id)
                    ->update([
                        'status' => 'disconnected',
                        'updated_at' => now(),
                    ]);
                \Log::info('Device marked disconnected', ['device_id' => $device_id]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Device webhook error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update device status from check
     */
    public function updateDeviceStatus()
    {
        try {
            $device_name = request('device_name');
            $status = request('status');
            $phone_number = request('phone_number');

            if (!$device_name || !$status) {
                return response()->json([
                    'success' => false,
                    'error' => 'device_name dan status harus diisi',
                ], 422);
            }

            // Try to find device, otherwise CREATE it
            $device = WhatsAppDevice::where('device_name', $device_name)->first();

            if (!$device) {
                // CREATE new device if not exists
                $device = WhatsAppDevice::create([
                    'device_id' => $device_name,
                    'device_name' => $device_name,
                    'status' => $status,
                    'phone_number' => $phone_number ?? '',  // Default to empty string if null
                ]);
                
                \Log::info("Device created via status update: {$device_name}", [
                    'phone' => $phone_number,
                    'status' => $status,
                ]);
            } else {
                // Update status
                $device->status = $status;
                
                // Update phone number jika diberikan
                if ($phone_number) {
                    $device->phone_number = $phone_number;
                }
                
                // Update last_connected_at jika status active
                if ($status === 'active') {
                    $device->last_connected_at = now();
                }
                
                $device->save();
                
                \Log::info("Device status updated: {$device_name} -> {$status}", [
                    'phone' => $phone_number,
                    'device_id' => $device->id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status device berhasil diupdate',
                'device' => $device,
            ]);
        } catch (\Exception $e) {
            \Log::error('Update device status error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test webhook
     */
    public function testWebhook()
    {
        try {
            $webhook_url = request('webhook_url');
            
            $payload = [
                'event' => 'test',
                'timestamp' => now()->toDateTimeString(),
                'message' => 'Ini adalah pesan uji webhook',
            ];

            $response = Http::timeout(10)->post($webhook_url, $payload);

            return response()->json([
                'success' => true,
                'status_code' => $response->status(),
                'message' => 'Webhook berhasil dikirim',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send WhatsApp message from dashboard
     */
    public function sendMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|string',
                'phone' => 'required|string',
                'message' => 'required|string|max:4096',
            ]);

            $deviceId = $validated['device_id'];
            $phone = $validated['phone'];
            $message = $validated['message'];

            // Find device
            $device = WhatsAppDevice::where('id', $deviceId)
                ->orWhere('device_name', $deviceId)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error' => 'Device tidak ditemukan',
                ], 404);
            }

            if ($device->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'error' => 'Device tidak aktif. Silakan reconnect di Settings.',
                ], 400);
            }

            // Send via Baileys
            $baileysUrl = config('services.baileys.api_url', 'http://localhost:3000');
            
            $response = Http::timeout(30)->post($baileysUrl . '/send-message', [
                'sessionId' => $device->device_name,
                'phone' => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                \Log::info('Saving message to database', [
                    'phone' => $phone,
                    'message' => $message,
                    'device_id' => $device->id,
                ]);
                
                // Save to message history
                BlastMessage::create([
                    'device_id' => $device->id,
                    'no_tlp' => $phone,
                    'message' => $message,
                    'status' => 'sent',
                    'sent_at' => now(),
                    'external_message_id' => $responseData['message_id'] ?? $responseData['messageId'] ?? null,
                    'response' => $responseData,
                ]);

                \Log::info('WhatsApp message sent', [
                    'device_id' => $device->id,
                    'phone' => $phone,
                    'message_length' => strlen($message),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Pesan berhasil dikirim',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal mengirim via backend: ' . $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Send WhatsApp error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send scheduled WhatsApp message
     */
    public function sendScheduledMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'recipients' => 'required|string',
                'message' => 'required|string|max:4096',
                'scheduled_at' => 'required|date_format:Y-m-d\TH:i',
            ]);

            $scheduledAt = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $validated['scheduled_at']);
            
            // Parse recipients (could be JSON or CSV)
            $recipients = [];
            try {
                $recipients = json_decode($validated['recipients'], true);
            } catch (\Exception $e) {
                $recipients = array_filter(array_map('trim', explode(',', $validated['recipients'])));
            }

            if (empty($recipients)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tidak ada penerima yang valid',
                ], 422);
            }

            $savedCount = 0;
            foreach ($recipients as $recipient) {
                // Create scheduled message
                BlastMessage::create([
                    'no_tlp' => $recipient,
                    'message' => $validated['message'],
                    'status' => 'scheduled',
                    'scheduled_at' => $scheduledAt,
                    'tipe_template' => 'scheduled',
                ]);
                $savedCount++;
            }

            \Log::info('Scheduled messages created', [
                'count' => $savedCount,
                'scheduled_at' => $scheduledAt,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pesan berhasil dijadwalkan untuk {$savedCount} penerima pada " . $scheduledAt->format('d/m/Y H:i'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Schedule message error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
