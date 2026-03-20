<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index('module_id');
            $table->unique(['module_id', 'key'], 'app_settings_module_key_unique');

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
