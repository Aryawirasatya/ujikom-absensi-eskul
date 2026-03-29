<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurricular_schedules', function (Blueprint $table) {

            // Drop foreign key dulu
            $table->dropForeign(['primary_coach_id']);
            $table->dropForeign(['backup_coach_id']);

            // Baru drop kolom
            $table->dropColumn([
                'start_time',
                'end_time',
                'primary_coach_id',
                'backup_coach_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_schedules', function (Blueprint $table) {

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedBigInteger('primary_coach_id')->nullable();
            $table->unsignedBigInteger('backup_coach_id')->nullable();

            $table->foreign('primary_coach_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('backup_coach_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
