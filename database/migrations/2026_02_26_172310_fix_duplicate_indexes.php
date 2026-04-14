<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurricular_schedules', function (Blueprint $table) {

            // drop duplicate
            $table->dropUnique('exs_esc_day_uq');

            // rename index jadi jelas
            $table->unique(
                ['extracurricular_id', 'day_of_week'],
                'uniq_schedule_per_day'
            );
        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_schedules', function (Blueprint $table) {

            $table->dropUnique('uniq_schedule_per_day');

            $table->unique(
                ['extracurricular_id', 'day_of_week'],
                'exs_esc_day_uq'
            );
        });
    }
};