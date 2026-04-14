<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluatee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->date('assessment_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->string('period_label'); // e.g. "Maret 2026", "Minggu 1 Mar 2026"
            $table->text('general_notes')->nullable();
            $table->timestamps();

            // Satu penilaian per evaluatee per periode per eskul
            $table->unique(['evaluator_id', 'evaluatee_id', 'extracurricular_id', 'period_label'], 'unique_assessment_per_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};