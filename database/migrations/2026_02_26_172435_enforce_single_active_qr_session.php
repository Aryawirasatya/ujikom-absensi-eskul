<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // matikan semua session duplicate
        DB::statement("
            UPDATE activity_qr_sessions a
            JOIN (
                SELECT activity_id, mode, MAX(id) as max_id
                FROM activity_qr_sessions
                WHERE is_active = 1
                GROUP BY activity_id, mode
            ) b
            ON a.activity_id = b.activity_id
            AND a.mode = b.mode
            SET a.is_active = 0
            WHERE a.id <> b.max_id
        ");

        // index bantu query session aktif
        DB::statement("
            CREATE INDEX idx_active_qr_session
            ON activity_qr_sessions(activity_id, mode, is_active)
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP INDEX idx_active_qr_session
            ON activity_qr_sessions
        ");
    }
};