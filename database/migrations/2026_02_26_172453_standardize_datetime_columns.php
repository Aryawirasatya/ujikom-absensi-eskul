<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE activities
            MODIFY started_at TIMESTAMP NULL,
            MODIFY ended_at TIMESTAMP NULL,
            MODIFY cancelled_at TIMESTAMP NULL
        ");

        DB::statement("
            ALTER TABLE attendances
            MODIFY checkin_at TIMESTAMP NULL,
            MODIFY checkout_at TIMESTAMP NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE activities
            MODIFY started_at DATETIME NULL,
            MODIFY ended_at DATETIME NULL,
            MODIFY cancelled_at DATETIME NULL
        ");

        DB::statement("
            ALTER TABLE attendances
            MODIFY checkin_at DATETIME NULL,
            MODIFY checkout_at DATETIME NULL
        ");
    }
};