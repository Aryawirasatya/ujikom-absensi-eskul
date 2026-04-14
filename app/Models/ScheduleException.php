<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    protected $fillable = [
        'schedule_id',
        'exception_date',
        'status',
        'reason',
        'reported_by',
        'approved_by_admin',
    ];

    /* ================= RELATIONS ================= */

    public function schedule()
    {
        return $this->belongsTo(ExtracurricularSchedule::class, 'schedule_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_admin');
    }

    /* ================= HELPERS ================= */

    public function isApproved(): bool
    {
        return !is_null($this->approved_by_admin);
    }
}
