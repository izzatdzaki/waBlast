<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppDevice extends Model
{
    protected $table = 'whatsapp_devices';

    protected $fillable = [
        'device_name',
        'phone_number',
        'status',
        'is_primary',
        'last_activity_at',
        'last_connected_at',
        'session_data',
        'device_info',
        'error_message',
        'created_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'last_activity_at' => 'datetime',
        'last_connected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'device_info' => 'array',
    ];

    /**
     * Get messages sent from this device
     */
    public function messages()
    {
        return $this->hasMany(BlastMessage::class, 'device_id');
    }

    /**
     * Scope: Get connected devices
     */
    public function scopeConnected($query)
    {
        return $query->where('status', 'active')->orWhere('status', 'active');
    }

    /**
     * Scope: Get primary device
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Get device status in Indonesian
     */
    public function getStatusLabel()
    {
        $statuses = [
            'active' => 'Terhubung',
            'inactive' => 'Tidak aktif',
            'connecting' => 'Sedang menghubung',
            'disconnected' => 'Terputus',
            'error' => 'Error',
            'connected' => 'Terhubung',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}

