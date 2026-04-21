<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('flexibility_items', function (Blueprint $table) {

             if (!Schema::hasColumn('flexibility_items', 'effect_value')) {
            $table->integer('effect_value')->nullable()->after('token_type');
        }

        });

        // ubah enum (pakai DB statement karena enum)
        DB::statement("
            ALTER TABLE flexibility_items 
            MODIFY token_type ENUM(
                'late_forgiveness',
                'free_alpha',
                'forget_checkout'
            )
        ");
    }

    public function down(): void
    {
        Schema::table('flexibility_items', function (Blueprint $table) {
            $table->dropColumn('effect_value');
        });
    }
};