<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_academics', function (Blueprint $table) {
            $table->string('academic_status', 20)
                  ->default('active')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_academics', function (Blueprint $table) {
            $table->enum('academic_status', [
                'active',
                'promoted',
                'repeated',
                'graduated',
            ])->default('active')->change();
        });
    }
};
