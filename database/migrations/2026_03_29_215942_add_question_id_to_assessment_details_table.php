<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_details', function (Blueprint $table) {
            // 1. Cek & Tambah question_id kalau belum ada
            if (!Schema::hasColumn('assessment_details', 'question_id')) {
                $table->foreignId('question_id')
                      ->after('assessment_id')
                      ->nullable() // nullable dulu biar data lama nggak bentrok
                      ->constrained('assessment_questions')
                      ->onDelete('cascade');
            }

            // 2. Kita biarkan category_id TETAP ADA. 
            // Kenapa? Karena di Controller lu (baris 361) lu masih pakai category_id buat query.
            // Kalau dihapus, laporan radar lu malah makin hancur.
        });
    }

    public function down(): void
    {
        Schema::table('assessment_details', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_details', 'question_id')) {
                $table->dropForeign(['question_id']);
                $table->dropColumn('question_id');
            }
        });
    }
};