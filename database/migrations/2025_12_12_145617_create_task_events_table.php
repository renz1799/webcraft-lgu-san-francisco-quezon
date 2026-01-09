<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_events', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Core relations
            $table->uuid('task_id');
            $table->uuid('actor_user_id');

            // 🔐 Immutable human-readable snapshots (for audit)
            $table->string('actor_name_snapshot', 255);
            $table->string('actor_username_snapshot', 255)->nullable();

            // Event classification
            // examples: created, assigned, reassigned, status_changed, comment
            $table->string('event_type');

            // Status transitions (nullable for non-status events)
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();

            // Comment / reason / explanation
            $table->text('note')->nullable();

            // Flexible metadata (IP, UA, reassignment info, etc.)
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes(); // optional but acceptable if protected

            // Indexes
            $table->index(['task_id', 'created_at']);
            $table->index('actor_user_id');
            $table->index('event_type');

            // Foreign keys
            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->cascadeOnDelete();

            // 🔒 Restrict deletion of users with audit history
            $table->foreign('actor_user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_events');
    }
};
