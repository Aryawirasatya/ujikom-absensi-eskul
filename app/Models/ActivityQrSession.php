<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityQrSession extends Model
{
    protected $fillable = [
        'activity_id',
        'mode',
        'opened_at',
        'duration_minutes',
        'expires_at',
        'late_tolerance_minutes',
        'secret_hash',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'opened_at'  => 'datetime',
        'expires_at' => 'datetime',
        'is_active'  => 'boolean'
    ];

    /* ================= RELATION ================= */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /* ================= SCOPE ================= */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}