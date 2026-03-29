<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentDetail extends Model
{
    protected $fillable = [
        'assessment_id',
        'category_id',   // <-- WAJIB DITAMBAHIN INI
        'question_id',   // <-- INI JUGA HARUS ADA
        'score',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Relasi ke pertanyaan
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }

    /**
     * Relasi ke kategori (biar gampang kalau mau narik nama kategori langsung)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'category_id');
    }
}