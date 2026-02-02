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
        Schema::table('extracurricular_schedules', function (Blueprint $table) {

        $table->time('start_time')->after('day_of_week');
        $table->time('end_time')->after('start_time');

        $table->unsignedBigInteger('primary_coach_id')->nullable()->after('end_time');
        $table->unsignedBigInteger('backup_coach_id')->nullable()->after('primary_coach_id');

        $table->foreign('primary_coach_id')
            ->references('id')
            ->on('users')
            ->nullOnDelete();

        $table->foreign('backup_coach_id')
            ->references('id')
            ->on('users')
            ->nullOnDelete();
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
