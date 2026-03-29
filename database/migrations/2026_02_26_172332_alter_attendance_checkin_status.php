<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE attendances
            MODIFY checkin_status
            ENUM('pending','on_time','late','absent')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE attendances
            MODIFY checkin_status
            ENUM('on_time','late','absent')
            NOT NULL DEFAULT 'absent'
        ");
    }
};