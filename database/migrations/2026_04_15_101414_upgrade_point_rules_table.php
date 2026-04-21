<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_rules', function (Blueprint $table) {

            // Target role (opsional)
            $table->string('target_role')->nullable()->after('rule_name');

            // Operator kondisi
            $table->enum('condition_operator', ['<', '>', '=', 'BETWEEN'])
                ->nullable()
                ->after('condition_field');

            // Value tambahan untuk BETWEEN
            $table->string('condition_value_2')
                ->nullable()
                ->after('condition_value');
        });
    }

    public function down(): void
    {
        Schema::table('point_rules', function (Blueprint $table) {
            $table->dropColumn([
                'target_role',
                'condition_operator',
                'condition_value_2'
            ]);
        });
    }
};