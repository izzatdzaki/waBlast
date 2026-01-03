<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasien';
    protected $primaryKey = 'no_rkm_medis';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function regPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    public function penjab()
    {
        return $this->belongsTo(Penjab::class, 'kd_pj', 'kd_pj');
    }

    /**
     * Get all WhatsApp messages sent to this patient
     */
    public function blastMessages()
    {
        return $this->hasMany(BlastMessage::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    /**
     * Get active phone number for WhatsApp messaging
     */
    public function getActivePhone()
    {
        // Return phone number in 62 format if exists
        if ($this->no_tlp) {
            $phone = preg_replace('/\D/', '', $this->no_tlp);
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            } elseif (substr($phone, 0, 2) !== '62') {
                $phone = '62' . $phone;
            }
            return $phone;
        }
        return null;
    }
}
