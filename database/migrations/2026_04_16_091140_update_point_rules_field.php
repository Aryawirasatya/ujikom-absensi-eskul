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
        Schema::table('point_rules', function (Blueprint $table) {

    $table->dropColumn('condition_field');

    });

    Schema::table('point_rules', function (Blueprint $table) {

        $table->string('condition_field')->after('target_role');

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
