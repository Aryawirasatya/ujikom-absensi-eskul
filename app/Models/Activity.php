<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'school_year_id',
        'extracurricular_id',
        'schedule_id',
        'session_owner_id',
        'type',
        'title',
        'description',
        'activity_date',
        'status',
        'attendance_phase',
        'attendance_mode', 
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function schedule()
    {
        return $this->belongsTo(ExtracurricularSchedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
public function qrSessions()
{
    return $this->hasMany(\App\Models\ActivityQrSession::class, 'activity_id');
}
    
}