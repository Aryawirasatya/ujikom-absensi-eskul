<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_rules', function (Blueprint $table) {

            // HAPUS YANG LAMA
            $table->dropColumn(['condition_field']);

        });

        Schema::table('point_rules', function (Blueprint $table) {

            // TAMBAH YANG BARU
            $table->enum('condition_field', [
                'checkin_time',
                'late_minutes'
            ])->after('target_role');

        });
    }

    public function down(): void
    {
        Schema::table('point_rules', function (Blueprint $table) {
            $table->dropColumn('condition_field');

            $table->enum('condition_field', [
                'checkin_status',
                'checkout_status',
                'final_status'
            ]);
        });
    }
};