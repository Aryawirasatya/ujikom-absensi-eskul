<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularMember extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'school_year_id',
        'user_id',
        'joined_at',
        'left_at',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function extracurricular()
{
    return $this->belongsTo(Extracurricular::class);
}
 

public function schoolYear()
{
    return $this->belongsTo(SchoolYear::class);
}

}
