<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name'); // Cth: "Disiplin Tepat Waktu"
            // Acuan kolom mana yang mau dicek di tabel attendances
            $table->enum('condition_field', ['checkin_status', 'checkout_status', 'final_status']);
            // Nilai statusnya. Cth: "on_time", "late", "not_completed", "alpha"
            $table->string('condition_value'); 
            // Jumlah poin (+5 untuk nambah, -3 untuk ngurang)
            $table->integer('point_modifier'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};