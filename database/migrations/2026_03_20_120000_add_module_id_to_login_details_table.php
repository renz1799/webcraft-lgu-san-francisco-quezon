<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_details', function (Blueprint $table) {
            $table->foreignUuid('module_id')
                ->nullable()
                ->after('user_id')
                ->constrained('modules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('login_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('module_id');
        });
    }
};
