<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // scope
            $table->uuid('module_id')->nullable();
            $table->uuid('department_id')->nullable();

            // who did it (nullable for system jobs / guests)
            $table->uuid('actor_id')->nullable();
            $table->string('actor_type')->nullable(); // usually App\Models\User

            // what was touched (polymorphic)
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable(); // keep string for UUID / generic IDs

            // what happened
            $table->string('action', 100);
            $table->string('message')->nullable();

            // request context
            $table->string('request_method', 10)->nullable();
            $table->text('request_url')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();

            // change sets
            $table->json('changes_old')->nullable();
            $table->json('changes_new')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index('module_id');
            $table->index('department_id');
            $table->index('actor_id');
            $table->index('action');
            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
            $table->index(['module_id', 'created_at']);
            $table->index(['department_id', 'created_at']);
            $table->index(['module_id', 'department_id', 'created_at']);

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->nullOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('actor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};