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
        Schema::create('point_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // EARN = Dapat poin dari absen, SPEND = Poin dipakai beli token, PENALTY = Kena minus poin karena telat/alpa
            $table->enum('transaction_type', ['EARN', 'SPEND', 'PENALTY']);
            $table->integer('amount'); // Nominal mutasi
            $table->integer('current_balance'); // Saldo sisa setelah mutasi ini (untuk mempermudah query saldo terakhir)
            $table->text('description')->nullable(); // Keterangan histori
            $table->timestamps(); // created_at ini otomatis jadi tanggal transaksinya
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_ledgers');
    }
};