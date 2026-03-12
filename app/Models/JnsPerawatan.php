<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JnsPerawatan extends Model
{
    use HasFactory;

    protected $table = 'jns_perawatan';
    protected $primaryKey = 'kd_jenis_prw';
    public $timestamps = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function rawatJlDr()
    {
        return $this->hasMany(RawatJlDr::class, 'kd_jenis_prw', 'kd_jenis_prw');
    }
}
