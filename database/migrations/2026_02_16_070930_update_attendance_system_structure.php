<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. UPDATE activities → tambah attendance_phase
        |--------------------------------------------------------------------------
        */
        Schema::table('activities', function (Blueprint $table) {
            $table->enum('attendance_phase', [
                'not_started',
                'checkin',
                'checkout',
                'finished'
            ])->default('not_started')
              ->after('status');
        });


        /*
        |--------------------------------------------------------------------------
        | 2. UPDATE attendances
        |--------------------------------------------------------------------------
        */

        // Ubah checkin_status jadi ENUM yang jelas
        DB::statement("
            ALTER TABLE attendances
            MODIFY checkin_status ENUM(
                'on_time',
                'late',
                'absent'
            ) NOT NULL DEFAULT 'absent'
        ");

        // Ubah final_status jadi ENUM resmi
        DB::statement("
            ALTER TABLE attendances
            MODIFY final_status ENUM(
                'hadir',
                'alpha',
                'izin',
                'sakit'
            ) NULL
        ");

        // Tambah checkout_status
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('checkout_status', [
                'completed',
                'not_completed'
            ])->nullable()->after('checkout_at');
        });


        /*
        |--------------------------------------------------------------------------
        | 3. UPDATE activity_qr_sessions → perjelas mode
        |--------------------------------------------------------------------------
        */
        DB::statement("
            ALTER TABLE activity_qr_sessions
            MODIFY mode ENUM('checkin', 'checkout') NOT NULL
        ");
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLLBACK
        |--------------------------------------------------------------------------
        */

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('attendance_phase');
        });

        DB::statement("
            ALTER TABLE attendances
            MODIFY checkin_status VARCHAR(255) NOT NULL DEFAULT 'alpa'
        ");

        DB::statement("
            ALTER TABLE attendances
            MODIFY final_status VARCHAR(255) NULL
        ");

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('checkout_status');
        });

        DB::statement("
            ALTER TABLE activity_qr_sessions
            MODIFY mode VARCHAR(255) NOT NULL
        ");
    }
};
