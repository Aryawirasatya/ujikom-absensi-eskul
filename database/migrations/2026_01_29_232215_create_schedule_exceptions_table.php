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
         Schema::create('schedule_exceptions', function (Blueprint $table) {

        $table->id();

        $table->unsignedBigInteger('schedule_id');
        $table->date('exception_date');

        $table->enum('status', ['cancelled', 'replaced']);

        $table->text('reason')->nullable();

        $table->unsignedBigInteger('reported_by');
        $table->unsignedBigInteger('approved_by_admin')->nullable();

        $table->timestamps();

        $table->foreign('schedule_id')
            ->references('id')
            ->on('extracurricular_schedules')
            ->cascadeOnDelete();

        $table->foreign('reported_by')
            ->references('id')
            ->on('users');

        $table->foreign('approved_by_admin')
            ->references('id')
            ->on('users');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
