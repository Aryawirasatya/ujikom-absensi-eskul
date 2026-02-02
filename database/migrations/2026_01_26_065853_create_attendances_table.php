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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete(); // siswa
            $table->dateTime('checkin_at')->nullable();
            $table->dateTime('checkout_at')->nullable();
            $table->string('checkin_status')->default('alpa');
            $table->string('final_status')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();  
            $table->timestamps();
            $table->unique(['activity_id', 'user_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
