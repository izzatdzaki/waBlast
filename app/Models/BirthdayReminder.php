<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthdayReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_rkm_medis',
        'message',
        'sender_phone',
        'receiver_phone',
        'birthday_date',
        'scheduled_date',
        'status',
        'response',
        'sent_at',
    ];

    protected $casts = [
        'birthday_date' => 'date',
        'scheduled_date' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get patient associated with this reminder
     */
    public function patient()
    {
        return $this->belongsTo(Pasien::class, 'no_rkm_medis', 'no_rkm_medis');
    }

    /**
     * Scope untuk pengiriman yang belum dikirim
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk ulang tahun hari ini
     */
    public function scopeTodayBirthday($query)
    {
        return $query->whereDate('birthday_date', now()->toDateString());
    }

    /**
     * Scope untuk ulang tahun minggu ini
     */
    public function scopeThisWeekBirthday($query)
    {
        return $query->whereBetween('birthday_date', [
            now()->toDateString(),
            now()->addDays(7)->toDateString(),
        ]);
    }
}
