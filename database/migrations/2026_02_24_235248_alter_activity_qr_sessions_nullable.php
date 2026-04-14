<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_qr_sessions', function (Blueprint $table) {
            $table->timestamp('opened_at')->nullable()->change();
            $table->timestamp('expires_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_qr_sessions', function (Blueprint $table) {
            $table->timestamp('opened_at')->nullable(false)->change();
            $table->timestamp('expires_at')->nullable(false)->change();
        });
    }
};