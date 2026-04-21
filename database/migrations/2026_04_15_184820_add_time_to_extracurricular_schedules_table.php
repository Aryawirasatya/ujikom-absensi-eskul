<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurricular_schedules', function (Blueprint $table) {

            // 🔥 WAJIB: jam mulai
            $table->time('start_time')->after('day_of_week');

            // 🟡 OPSIONAL: kapan boleh mulai scan
            $table->integer('checkin_open_before_minutes')
                  ->default(30)
                  ->after('start_time');

        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_schedules', function (Blueprint $table) {

            $table->dropColumn([
                'start_time',
                'checkin_open_before_minutes'
            ]);

        });
    }
};