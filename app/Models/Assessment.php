<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'evaluator_id',
        'evaluatee_id',
        'extracurricular_id',
        'assessment_date',
        'period_type',
        'period_label',
        'general_notes',
    ];

    protected $casts = [
        'assessment_date' => 'date',
    ];

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function evaluatee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    public function extracurricular(): BelongsTo
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(AssessmentDetail::class);
    }

    /**
     * Hitung rata-rata skor assessment ini
     */
    public function getAverageScoreAttribute(): float
    {
        return $this->details->avg('score') ?? 0;
    }

    /**
     * Generate period_label otomatis berdasarkan period_type & tanggal
     */
    public static function generatePeriodLabel(string $periodType, \Carbon\Carbon $date): string
    {
        return match ($periodType) {
            'daily'   => $date->translatedFormat('d F Y'),
            'weekly'  => 'Minggu ' . $date->weekOfMonth . ' ' . $date->translatedFormat('F Y'),
            'monthly' => $date->translatedFormat('F Y'),
            default   => $date->translatedFormat('F Y'),
        };
    }
}