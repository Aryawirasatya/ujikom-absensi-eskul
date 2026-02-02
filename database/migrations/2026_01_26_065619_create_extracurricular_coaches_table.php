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
        Schema::create('extracurricular_coaches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete(); // pembina

            $table->boolean('is_primary')->default(true);

            $table->timestamps();

            $table->unique(['extracurricular_id', 'user_id'], 'exs_esc_day_uq');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_coaches');
    }
};
