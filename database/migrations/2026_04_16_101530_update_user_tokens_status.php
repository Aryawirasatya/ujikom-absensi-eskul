<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE user_tokens 
            MODIFY status ENUM('AVAILABLE','USED','EXPIRED') 
            DEFAULT 'AVAILABLE'
        ");
    }

    public function down(): void {}
};
