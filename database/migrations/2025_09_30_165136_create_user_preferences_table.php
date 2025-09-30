<?php

// database/migrations/2025_09_29_000001_create_user_preferences_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->json('theme_style')->nullable(); // {"mode":"light","dir":"ltr",...}
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('user_preferences');
    }
};
