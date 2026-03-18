<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // scope
            $table->uuid('module_id')->nullable();
            $table->uuid('department_id')->nullable();

            $table->uuid('notifiable_user_id');        // receiver
            $table->uuid('actor_user_id')->nullable(); // who triggered it

            $table->string('type', 100); // e.g. task_assigned
            $table->string('title');
            $table->text('message')->nullable();

            // link back to entity (task, document, etc.)
            $table->string('entity_type', 100)->nullable();
            $table->uuid('entity_id')->nullable();

            $table->json('data')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('module_id');
            $table->index('department_id');
            $table->index('notifiable_user_id');
            $table->index('actor_user_id');
            $table->index('type');
            $table->index(['entity_type', 'entity_id'], 'notif_entity_idx');
            $table->index('read_at');
            $table->index(['notifiable_user_id', 'read_at'], 'notif_user_read_idx');
            $table->index(['notifiable_user_id', 'type', 'created_at'], 'notif_user_type_created_idx');
            $table->index(['module_id', 'notifiable_user_id', 'created_at'], 'notif_module_user_created_idx');
            $table->index(['department_id', 'notifiable_user_id', 'created_at'], 'notif_dept_user_created_idx');
            $table->index(
                ['module_id', 'department_id', 'notifiable_user_id', 'created_at'],
                'notif_module_dept_user_created_idx'
            );

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->nullOnDelete();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();

            $table->foreign('notifiable_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('actor_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};