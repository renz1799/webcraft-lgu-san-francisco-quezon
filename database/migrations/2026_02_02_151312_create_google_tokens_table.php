<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('module_id');
            $table->uuid('department_id');
            $table->uuid('connected_by_user_id')->nullable();

            $table->string('provider', 100)->default('google_drive');

            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('module_id');
            $table->index('department_id');
            $table->index('connected_by_user_id');
            $table->index('provider');
            $table->index(['module_id', 'department_id']);
            $table->index(['module_id', 'provider']);
            $table->index(['department_id', 'provider']);

            $table->unique(
                ['module_id', 'department_id', 'provider'],
                'google_tokens_module_department_provider_unique'
            );

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->cascadeOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->cascadeOnDelete();

            $table->foreign('connected_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_tokens');
    }
};