<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentPeriod extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'period_label',
        'period_type',
        'status',
        'closed_at',
        'closed_by'
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }
}
