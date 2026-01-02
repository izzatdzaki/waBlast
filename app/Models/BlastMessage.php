<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlastMessage extends Model
{
    protected $table = 'blast_messages';
    protected $fillable = [
        'template_id',
        'jadwal_kontrol_id',
        'no_surat',
        'no_rkm_medis',
        'nama_pasien',
        'no_tlp',
        'pesan',
        'status',
        'response',
        'sent_at',
        'scheduled_at',
        'notes'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'response' => 'array'
    ];
}    public function template()
    {
        return $this->belongsTo(BlastTemplate::class, 'template_id');
    }
}
