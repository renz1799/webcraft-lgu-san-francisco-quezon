<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_events', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('task_id');
            $table->uuid('actor_user_id');

            // event types: created, assigned, reassigned, status_changed, comment, escalated (later)
            $table->string('event_type');

            // only used for status events (nullable for comment-only events)
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();

            // note / reason / comment
            $table->text('note')->nullable();

            // future-proof meta (reassign info, escalation level, etc.)
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['task_id', 'created_at']);
            $table->index(['event_type']);

            // If you want FK constraints (recommended)
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_events');
    }
};
