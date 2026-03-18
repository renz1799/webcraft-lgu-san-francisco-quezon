<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_events', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // core relations
            $table->uuid('task_id');
            $table->uuid('actor_user_id');

            // immutable human-readable snapshots
            $table->string('actor_name_snapshot', 255);
            $table->string('actor_username_snapshot', 255)->nullable();

            // event classification
            $table->string('event_type');

            // status transitions
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();

            // comment / reason / explanation
            $table->text('note')->nullable();

            // flexible metadata
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['task_id', 'created_at']);
            $table->index('actor_user_id');
            $table->index('event_type');

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->cascadeOnDelete();

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