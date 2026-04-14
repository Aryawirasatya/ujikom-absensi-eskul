<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE attendances
            MODIFY final_status ENUM(
                'hadir',
                'telat',
                'izin',
                'sakit',
                'alpha',
                'libur'
            ) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE attendances
            MODIFY final_status ENUM(
                'hadir',
                'telat',
                'izin',
                'sakit',
                'alpha'
            ) NULL
        ");
    }
};