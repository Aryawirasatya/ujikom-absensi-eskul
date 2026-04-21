<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🔥 STEP 1: convert value lama dulu
        DB::statement("
            UPDATE user_tokens 
            SET status = 'USED' 
            WHERE status NOT IN ('INVENTORY','ACTIVE','USED')
        ");

        // 🔥 STEP 2: baru ubah ENUM
        DB::statement("
            ALTER TABLE user_tokens 
            MODIFY status ENUM('INVENTORY','ACTIVE','USED') 
            NOT NULL DEFAULT 'INVENTORY'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE user_tokens 
            MODIFY status ENUM('AVAILABLE','USED') NOT NULL
        ");
    }
};