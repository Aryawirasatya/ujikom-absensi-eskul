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
        Schema::create('assessment_periods', function (Blueprint $table) {
    $table->id();

    $table->foreignId('extracurricular_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->string('period_label'); // contoh: Mar 2026
    $table->string('period_type')->default('monthly');

    $table->enum('status', ['open','closed'])->default('open');

    $table->timestamp('closed_at')->nullable();
    $table->foreignId('closed_by')->nullable()->constrained('users');

    $table->timestamps();

    $table->unique(['extracurricular_id','period_label']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_periods');
    }
};
