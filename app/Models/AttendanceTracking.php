<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTracking extends Model
{
    use HasFactory;

    protected $table = 'attendance_tracking';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'no_surat',
        'no_rkm_medis',
        'nama_pasien',
        'tanggal_rencana',
        'status_kehadiran', // 'belum_datang', 'sudah_datang'
        'waktu_datang',
        'keterangan',
    ];

    public function bridgingSuratKontrolBpjs()
    {
        return $this->belongsTo(BridgingSuratKontrolBpjs::class, 'no_surat', 'no_surat');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }
}
