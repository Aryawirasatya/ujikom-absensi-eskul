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
        Schema::create('activity_qr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('mode'); 
            $table->dateTime('opened_at');
            $table->integer('duration_minutes')->default(10);
            $table->dateTime('expires_at');
            $table->integer('late_tolerance_minutes')->nullable();  
            $table->boolean('is_active')->default(true);
            $table->dateTime('closed_at')->nullable();
            $table->string('secret_hash');
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_qr_sessions');
    }
};
