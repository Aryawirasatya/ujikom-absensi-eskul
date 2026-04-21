<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Menggunakan DB statement karena change() pada ENUM terkadang bermasalah di beberapa versi Doctrine
        DB::statement("ALTER TABLE point_rules MODIFY COLUMN condition_operator ENUM('<', '>', '=', '<=', '>=', 'BETWEEN')");
    }

    public function down(): void
    {
        // Kembalikan ke 4 operator awal jika diperlukan rollback
        DB::statement("ALTER TABLE point_rules MODIFY COLUMN condition_operator ENUM('<', '>', '=', 'BETWEEN')");
    }
};