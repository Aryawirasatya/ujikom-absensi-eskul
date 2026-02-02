<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('extracurricular_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->tinyInteger('day_of_week'); // 1-7
            $table->boolean('is_active')->default(true);
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->unique(['extracurricular_id', 'day_of_week'], 'exs_esc_day_uq');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_schedules');
    }
};
