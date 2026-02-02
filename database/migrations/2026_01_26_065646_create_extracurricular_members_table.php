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
        Schema::create('extracurricular_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('extracurricular_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete(); // siswa

            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->string('status')->default('active'); // active|inactive

            $table->timestamps();

            $table->unique(['extracurricular_id', 'school_year_id', 'user_id'], 'exs_esc_day_uq');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_members');
    }
};
