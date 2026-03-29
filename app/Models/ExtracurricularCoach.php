<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtracurricularCoach extends Model
{
    protected $fillable = [
        'extracurricular_id',
        'user_id',
        'is_primary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }
}
