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
        Schema::table('activities', function (Blueprint $table) {

        $table->unsignedBigInteger('schedule_id')->nullable()->after('extracurricular_id');
        $table->unsignedBigInteger('session_owner_id')->nullable()->after('schedule_id');

        $table->foreign('schedule_id')
            ->references('id')
            ->on('extracurricular_schedules')
            ->nullOnDelete();

        $table->foreign('session_owner_id')
            ->references('id')
            ->on('users')
            ->nullOnDelete();
        $table->unique(['extracurricular_id', 'activity_date', 'schedule_id']);

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
