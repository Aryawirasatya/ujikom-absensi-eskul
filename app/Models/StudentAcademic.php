<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAcademic extends Model
{
    protected $fillable = [
        'user_id',
        'school_year_id',
        'grade',
        'class_label',
        'academic_status',
    ];

    protected $casts = [
        'grade' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
