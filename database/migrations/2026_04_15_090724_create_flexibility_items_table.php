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
        Schema::create('flexibility_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');  
            $table->enum('token_type', ['late_forgiveness', 'forget_checkout', 'free_alpha']);
            $table->integer('point_cost'); // Harga beli token pake poin
            $table->integer('stock_limit')->nullable(); // Opsional, misal cuma boleh beli 1x sebulan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flexibility_items');
    }
};