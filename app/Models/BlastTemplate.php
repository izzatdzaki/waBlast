<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlastTemplate extends Model
{
    protected $table = 'blast_templates';
    protected $fillable = [
        'nama_template',
        'isi_pesan',
        'tipe_template',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function messages()
    {
        return $this->hasMany(BlastMessage::class, 'template_id');
    }
}
