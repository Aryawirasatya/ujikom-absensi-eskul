<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

 
return new class extends Migration {
    public function up(): void
    {
        // ===== schedules =====
        Schema::table('extracurricular_schedules', function (Blueprint $table) {
            $table->dropColumn('checkin_open_before_minutes'); // ❌ hapus

            $table->time('checkin_open_at')->after('start_time'); // ✅ baru
        });

        // ===== activities =====
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('checkin_open_before_minutes'); // ❌ hapus

            $table->dateTime('checkin_open_at')->nullable()->after('started_at'); // ✅ baru
        });
    }

    public function down(): void
    {
        Schema::table('extracurricular_schedules', function (Blueprint $table) {
            $table->integer('checkin_open_before_minutes')->nullable();
            $table->dropColumn('checkin_open_at');
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->integer('checkin_open_before_minutes')->nullable();
            $table->dropColumn('checkin_open_at');
        });
    }
};