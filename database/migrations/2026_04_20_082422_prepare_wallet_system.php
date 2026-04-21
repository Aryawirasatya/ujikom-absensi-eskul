<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
    Schema::table('users', function (Blueprint $table) {
        // Tambah kolom saldo jika belum ada
        if (!Schema::hasColumn('users', 'point_balance')) {
            $table->integer('point_balance')->default(0)->after('password');
        }
    });

    Schema::table('flexibility_items', function (Blueprint $table) {
        // Pastikan point_cost tidak bisa negatif
        $table->integer('point_cost')->unsigned()->change();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
