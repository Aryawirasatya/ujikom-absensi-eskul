<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_academics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('grade'); // 7, 8, 9
            $table->string('class_label')->nullable(); // 7A, 8B, dst

            $table->enum('academic_status', [
                'active',
                'promoted',
                'repeated',
                'graduated',
            ])->default('active');

            $table->timestamps();

            // 1 siswa hanya boleh 1 record per tahun ajaran
            $table->unique(['user_id', 'school_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_academics');
    }
};
