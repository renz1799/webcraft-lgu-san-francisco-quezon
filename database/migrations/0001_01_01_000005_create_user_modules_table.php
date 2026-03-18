<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->uuid('module_id');
            $table->uuid('department_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('module_id');
            $table->index('department_id');
            $table->index('is_active');
            $table->index(['user_id', 'module_id']);
            $table->index(['module_id', 'department_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['module_id', 'is_active']);

            $table->unique(
                ['user_id', 'module_id', 'department_id'],
                'user_modules_user_module_department_unique'
            );

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->cascadeOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_modules');
    }
};