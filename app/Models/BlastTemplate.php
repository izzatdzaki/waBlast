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
        'category',
        'placeholder_variables',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'placeholder_variables' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get all messages using this template
     */
    public function messages()
    {
        return $this->hasMany(BlastMessage::class, 'template_id');
    }

    /**
     * Get preview with sample data
     */
    public function getPreview($sampleData = [])
    {
        $defaultSample = [
            'nama_pasien' => 'Budi Santoso',
            'tanggal_jadwal' => date('d-m-Y'),
            'jam_jadwal' => '14:00',
            'poliklinik' => 'Umum',
            'dokter' => 'Dr. Surya',
            'no_surat' => 'SEP-2024-001',
            'no_rkm_medis' => '123456',
            'tanggal_pesan' => date('d-m-Y'),
            'jam_pesan' => date('H:i'),
        ];

        $data = array_merge($defaultSample, $sampleData);
        $preview = $this->isi_pesan;

        foreach ($data as $key => $value) {
            $preview = str_replace('{' . $key . '}', $value, $preview);
        }

        return $preview;
    }

    /**
     * Get available placeholders for this template
     */
    public function getAvailablePlaceholders()
    {
        $placeholders = [];
        preg_match_all('/\{([^}]+)\}/', $this->isi_pesan, $matches);

        if (!empty($matches[1])) {
            $placeholders = $matches[1];
        }

        return $placeholders;
    }
}
