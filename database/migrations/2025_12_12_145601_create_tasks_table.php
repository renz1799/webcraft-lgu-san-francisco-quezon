<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // scope
            $table->uuid('module_id')->nullable();
            $table->uuid('department_id')->nullable();

            // what to do
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->nullable(); // e.g. release_sticker, print_we_bill

            // current snapshot
            $table->string('status')->default('pending'); // pending, in_progress, done, cancelled
            $table->unsignedTinyInteger('priority')->default(0);

            // ownership
            $table->uuid('created_by_user_id');
            $table->uuid('assigned_to_user_id')->nullable();

            // generic subject link
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();

            // timeline
            $table->timestamp('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // flexible payload
            $table->json('data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('module_id');
            $table->index('department_id');
            $table->index(['module_id', 'status']);
            $table->index(['department_id', 'status']);
            $table->index(['assigned_to_user_id', 'status']);
            $table->index(['module_id', 'assigned_to_user_id']);
            $table->index(['department_id', 'assigned_to_user_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('due_at');

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->nullOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('created_by_user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('assigned_to_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};