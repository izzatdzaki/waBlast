<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BridgingSuratKontrolBpjs extends Model
{
    use HasFactory;

    protected $table = 'bridging_surat_kontrol_bpjs';
    protected $primaryKey = 'no_surat';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function bridgingSep()
    {
        return $this->belongsTo(BridgingSep::class, 'no_sep', 'no_sep');
    }

    public function pasien()
    {
        return $this->through('bridgingSep.regPeriksa.pasien');
    }
}
