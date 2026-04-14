<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration ini menambahkan kolom pengaturan global visibilitas penilaian ke siswa
// di tabel extracurriculars (tiap eskul bisa punya setting sendiri)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {
            $table->boolean('show_assessment_to_student')->default(false)->after('is_active');
            $table->enum('assessment_period', ['daily', 'weekly', 'monthly'])->default('monthly')->after('show_assessment_to_student');
        });
    }

    public function down(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {
            $table->dropColumn(['show_assessment_to_student', 'assessment_period']);
        });
    }
};