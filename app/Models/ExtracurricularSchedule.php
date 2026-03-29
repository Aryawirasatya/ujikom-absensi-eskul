<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularSchedule extends Model
{
    protected $table = 'extracurricular_schedules';

    protected $fillable = [
        'extracurricular_id',
        'day_of_week',
        'start_time',
        'end_time',
        'primary_coach_id',
        'is_active',
        'notes',
    ];

    /* ================= RELATIONS ================= */

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function primaryCoach()
    {
        return $this->belongsTo(User::class, 'primary_coach_id');
    }


    public function exceptions()
    {
        return $this->hasMany(ScheduleException::class, 'schedule_id');
    }

     public function activeCoach()
    {
        if ($this->primaryCoach?->is_active) {
            return $this->primaryCoach;
        }


        return null;
    }

    
    
}
