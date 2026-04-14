<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'category_id',
        'question',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'category_id');
    }

    
}