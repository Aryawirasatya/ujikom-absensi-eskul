<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // contoh konversi
        DB::table('point_rules')
            ->where('condition_value', 'on_time')
            ->update([
                'condition_field' => 'late_minutes',
                'condition_operator' => '=',
                'condition_value' => '0'
            ]);

        DB::table('point_rules')
            ->where('condition_value', 'late')
            ->update([
                'condition_field' => 'late_minutes',
                'condition_operator' => '>',
                'condition_value' => '0'
            ]);
    }

    public function down(): void {}
};