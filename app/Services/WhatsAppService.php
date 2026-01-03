<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    private $apiUrl;
    private $timeout = 30;

    public function __construct()
    {
        $this->apiUrl = config('services.baileys.api_url') ?? 'http://localhost:3000';
    }

    /**
     * Send immediate WhatsApp message
     * 
     * @param string $phoneNumber Phone number in format 62xxx
     * @param string $message Message content
     * @param array $metadata Additional metadata
     * @return array Response with message_id
     */
    public function sendMessage(string $phoneNumber, string $message, array $metadata = [])
    {
        try {
            Log::info('WhatsApp: Sending message to ' . $phoneNumber);

            // Get active device from database
            $device = \DB::table('whatsapp_devices')
                ->where('status', 'active')
                ->first();

            if (!$device) {
                throw new Exception('No active WhatsApp device found. Please connect device first.');
            }

            Log::info('Using device: ' . $device->device_name);

            $response = Http::timeout($this->timeout)->post(
                $this->apiUrl . '/send-message',
                [
                    'phone' => $phoneNumber,
                    'message' => $message,
                    'sessionId' => $device->device_name,
                    'metadata' => $metadata,
                ]
            );

            if (!$response->successful()) {
                Log::error('WhatsApp API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone' => $phoneNumber,
                ]);
                throw new Exception('Failed to send message: ' . $response->body());
            }

            $data = $response->json();
            Log::info('WhatsApp: Message sent successfully', [
                'message_id' => $data['message_id'] ?? null,
                'phone' => $phoneNumber,
            ]);

            return $data;
        } catch (Exception $e) {
            Log::error('WhatsApp Service Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send message using specific device
     * 
     * @param string $phoneNumber Phone number in format 62xxx
     * @param string $message Message content
     * @param string $deviceName Device session ID
     * @param array $metadata Additional metadata
     * @return array Response with message_id
     */
    public function sendMessageWithDevice(string $phoneNumber, string $message, string $deviceName, array $metadata = [])
    {
        try {
            Log::info('WhatsApp: Sending message to ' . $phoneNumber . ' via device: ' . $deviceName);

            $response = Http::timeout($this->timeout)->post(
                $this->apiUrl . '/send-message',
                [
                    'phone' => $phoneNumber,
                    'message' => $message,
                    'sessionId' => $deviceName,
                    'metadata' => $metadata,
                ]
            );

            if (!$response->successful()) {
                Log::error('WhatsApp API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone' => $phoneNumber,
                    'device' => $deviceName,
                ]);
                throw new Exception('Gagal mengirim pesan: ' . $response->body());
            }

            $data = $response->json();
            Log::info('WhatsApp: Message sent successfully', [
                'message_id' => $data['message_id'] ?? null,
                'phone' => $phoneNumber,
                'device' => $deviceName,
            ]);

            return $data;
        } catch (Exception $e) {
            Log::error('WhatsApp Device Send Error: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Send scheduled message
     * 
     * @param string $phoneNumber Phone number
     * @param string $message Message content
     * @param string $scheduledAt Schedule time (Y-m-d H:i:s)
     * @param array $metadata Additional metadata
     * @return array Response
     */
    public function sendScheduledMessage(string $phoneNumber, string $message, string $scheduledAt, array $metadata = [])
    {
        try {
            Log::info('WhatsApp: Scheduling message to ' . $phoneNumber . ' at ' . $scheduledAt);

            $response = Http::timeout($this->timeout)->post(
                $this->apiUrl . '/send-scheduled',
                [
                    'phone' => $phoneNumber,
                    'message' => $message,
                    'scheduled_at' => $scheduledAt,
                    'metadata' => $metadata,
                ]
            );

            if (!$response->successful()) {
                Log::error('WhatsApp API Error on scheduled send', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Failed to schedule message: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('WhatsApp Scheduled Send Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check message delivery status
     * 
     * @param string $messageId Message ID from Baileys
     * @return array Status information
     */
    public function checkStatus(string $messageId)
    {
        try {
            Log::info('WhatsApp: Checking status for message ' . $messageId);

            $response = Http::timeout($this->timeout)->get(
                $this->apiUrl . '/message-status/' . $messageId
            );

            if (!$response->successful()) {
                Log::warning('WhatsApp: Status check failed', [
                    'status' => $response->status(),
                    'message_id' => $messageId,
                ]);
                return ['status' => 'unknown'];
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('WhatsApp Status Check Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get message delivery history
     * 
     * @param array $filters Filter options (status, phone, date_from, date_to)
     * @param int $limit Results limit
     * @param int $offset Pagination offset
     * @return array Message history
     */
    public function getMessageHistory(array $filters = [], int $limit = 50, int $offset = 0)
    {
        try {
            $response = Http::timeout($this->timeout)->get(
                $this->apiUrl . '/messages',
                array_merge($filters, ['limit' => $limit, 'offset' => $offset])
            );

            if (!$response->successful()) {
                Log::warning('WhatsApp: History fetch failed', ['status' => $response->status()]);
                return ['messages' => [], 'total' => 0];
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('WhatsApp History Fetch Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Replace template variables with actual values
     * 
     * @param string $template Template string with {placeholders}
     * @param array $variables Variables to replace
     * @return string Processed message
     */
    public function replaceTemplateVariables(string $template, array $variables = [])
    {
        $message = $template;

        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value ?? '', $message);
        }

        return $message;
    }

    /**
     * Format phone number to 62 format (without +)
     * 
     * @param string $phone Phone number
     * @return string Formatted phone
     */
    public function formatPhoneNumber(string $phone)
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Validate Indonesian phone number
     * 
     * @param string $phone Phone number
     * @return bool Valid or not
     */
    public function validatePhoneNumber(string $phone)
    {
        $phone = preg_replace('/\D/', '', $phone);

        // Must start with 62 and have 10-13 more digits (62 + 8-13 digits = 10-15 total)
        return preg_match('/^62[8-9]\d{7,12}$/', $phone);
    }

    /**
     * Verify Baileys API health
     * 
     * @return bool API is reachable
     */
    public function healthCheck()
    {
        try {
            $response = Http::timeout(5)->get($this->apiUrl . '/health');
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('WhatsApp API health check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bulk messages
     * 
     * @param array $messages Array of ['phone' => '', 'message' => '', 'metadata' => []]
     * @return array Results array
     */
    public function sendBulkMessages(array $messages)
    {
        $results = [];

        foreach ($messages as $msg) {
            try {
                $result = $this->sendMessage(
                    $msg['phone'],
                    $msg['message'],
                    $msg['metadata'] ?? []
                );
                $results[] = array_merge($result, ['success' => true]);
            } catch (Exception $e) {
                $results[] = [
                    'phone' => $msg['phone'],
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
