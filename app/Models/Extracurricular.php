<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extracurricular extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_active', 
    'show_assessment_to_student'
    ];

    public function coaches()
    {
        return $this->hasMany(ExtracurricularCoach::class);
    }

    public function primaryCoach()
    {
        return $this->hasOne(ExtracurricularCoach::class)
            ->where('is_primary', 1);
    }
    public function schedules()
{
    return $this->hasMany(ExtracurricularSchedule::class);
}
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function members()
{
    return $this->hasMany(ExtracurricularMember::class);
}
public function periods()
{
    return $this->hasMany(AssessmentPeriod::class);
}

}
