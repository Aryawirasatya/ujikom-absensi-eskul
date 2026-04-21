<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'activity_id',
        'user_id',
        'checkin_at',
        'checkout_at',
        'checkin_status',
        'final_status',
        'note',
        'early_minutes',
        'updated_by',
        'attendance_source',
    ];

    protected $casts = [
        'checkin_at'  => 'datetime',
        'checkout_at' => 'datetime',
    ];

    /**
     * RELATION
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isManual()
    {
        return in_array($this->final_status,['izin','sakit','hadir']);
    }

    // ==========================================
    // RELASI DOMPET INTEGRITAS
    // ==========================================

    // Relasi untuk mengecek apakah absensi ini diselamatkan oleh token
    public function appliedToken()
    {
        return $this->hasOne(\App\Models\UserToken::class, 'used_at_attendance_id');
    }
}