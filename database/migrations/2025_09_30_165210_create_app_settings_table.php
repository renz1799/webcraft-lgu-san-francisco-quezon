<?php

// database/migrations/2025_09_29_000002_create_app_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();           // e.g. "theme.colors"
            $table->json('value')->nullable();          // {"primary":"#635BFF",...}
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('app_settings');
    }
};
