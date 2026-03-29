<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('assessment_categories')->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1-5
            $table->timestamps();

            $table->unique(['assessment_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_details');
    }
};