<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // what to do
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->nullable(); // e.g. release_sticker, print_we_bill

            // current snapshot
            $table->string('status')->default('pending'); // pending, in_progress, done, cancelled
            $table->unsignedTinyInteger('priority')->default(0); // optional: 0 normal, 1 high, etc.

            // ownership
            $table->uuid('created_by_user_id');
            $table->uuid('assigned_to_user_id')->nullable();

            // generic subject link (core-friendly)
            $table->string('subject_type')->nullable(); // e.g. inventory_items
            $table->uuid('subject_id')->nullable();     // record uuid

            // timeline-related timestamps
            $table->timestamp('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // flexible payload
            $table->json('data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to_user_id', 'status']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
