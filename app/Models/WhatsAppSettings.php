<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppSettings extends Model
{
    protected $table = 'whatsapp_settings';
    protected $fillable = [
        // Connection settings
        'baileys_url',
        'baileys_status',
        
        // Device settings
        'default_device_id',
        'device_check_interval',
        
        // Webhook settings
        'webhook_url',
        'webhook_enabled',
        'webhook_secret',
        
        // Message settings
        'enable_auto_reply',
        'auto_reply_message',
        'message_retention_days',
        'max_message_length',
        
        // API settings
        'api_rate_limit',
        'api_timeout',
        'api_retry_attempts',
        'api_retry_delay',
    ];

    protected $casts = [
        'baileys_status' => 'boolean',
        'webhook_enabled' => 'boolean',
        'enable_auto_reply' => 'boolean',
        'device_check_interval' => 'integer',
        'message_retention_days' => 'integer',
        'max_message_length' => 'integer',
        'api_rate_limit' => 'integer',
        'api_timeout' => 'integer',
        'api_retry_attempts' => 'integer',
        'api_retry_delay' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get or create settings (singleton pattern)
     */
    public static function getSettings()
    {
        return self::firstOrCreate(
            [],
            [
                'baileys_url' => config('services.baileys.api_url', 'http://localhost:3000'),
                'baileys_status' => false,
                'default_device_id' => null,
                'device_check_interval' => 30,
                'webhook_url' => config('services.baileys.webhook_url'),
                'webhook_enabled' => true,
                'webhook_secret' => null,
                'enable_auto_reply' => false,
                'auto_reply_message' => 'Terima kasih atas pesan Anda. Kami akan merespons sesegera mungkin.',
                'message_retention_days' => 30,
                'max_message_length' => 4096,
                'api_rate_limit' => config('services.baileys.rate_limit', 20),
                'api_timeout' => config('services.baileys.timeout', 30),
                'api_retry_attempts' => 3,
                'api_retry_delay' => 5,
            ]
        );
    }

    /**
     * Get a specific setting value
     */
    public static function getSetting($key, $default = null)
    {
        $settings = self::getSettings();
        return $settings->$key ?? $default;
    }

    /**
     * Update a setting value
     */
    public static function updateSetting($key, $value)
    {
        $settings = self::getSettings();
        $settings->update([$key => $value]);
        return $settings;
    }

    /**
     * Update multiple settings
     */
    public static function updateSettings($data)
    {
        $settings = self::getSettings();
        $settings->update($data);
        return $settings;
    }
}
