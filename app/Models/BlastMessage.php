<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlastMessage extends Model
{
    protected $table = 'blast_messages';
    protected $fillable = [
        'template_id',
        'device_id',
        'jadwal_kontrol_id',
        'no_surat',
        'no_rkm_medis',
        'nama_pasien',
        'no_tlp',
        'message',
        'pesan',
        'status',
        'response',
        'sent_at',
        'scheduled_at',
        'notes',
        'external_message_id',
        'delivered_at',
        'read_at',
        'tipe_template',
        'template_variables',
        'created_by',
        'pasien_id'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'response' => 'array',
        'template_variables' => 'array'
    ];

    /**
     * Get the template associated with this message
     */
    public function template()
    {
        return $this->belongsTo(BlastTemplate::class, 'template_id');
    }

    /**
     * Get the patient associated with this message
     */
    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    /**
     * Get the WhatsApp device used for this message
     */
    public function device()
    {
        return $this->belongsTo(WhatsAppDevice::class, 'device_id');
    }

    /**
     * Scope: Get pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get scheduled messages
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope: Get failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get sent messages
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Check if message can be resent
     */
    public function canBeResent()
    {
        return in_array($this->status, ['failed', 'pending']);
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabel()
    {
        $labels = [
            'pending' => 'Menunggu',
            'scheduled' => 'Dijadwalkan',
            'sent' => 'Terkirim',
            'delivered' => 'Diterima',
            'read' => 'Dibaca',
            'failed' => 'Gagal'
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
