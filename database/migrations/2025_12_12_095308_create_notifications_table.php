<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('notifiable_user_id')->index(); // the receiver
            $table->uuid('actor_user_id')->nullable()->index(); // who triggered it (optional)

            $table->string('type', 100)->index(); // e.g. task_assigned
            $table->string('title');
            $table->text('message')->nullable();

            // Link back to entity (task, enrollment, etc.)
            $table->string('entity_type', 100)->nullable()->index(); // e.g. tasks
            $table->uuid('entity_id')->nullable()->index();

            $table->json('data')->nullable(); // extra payload

            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            // (optional) helps prevent duplicates (adjust based on your needs)
            $table->index(['notifiable_user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
