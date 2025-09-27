<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // who did it (nullable for system jobs / guests)
            $table->uuid('actor_id')->nullable()->index();
            $table->string('actor_type')->nullable(); // usually App\Models\User

            // what was touched (polymorphic)
            $table->string('subject_type')->nullable()->index();
            $table->string('subject_id')->nullable()->index(); // keep string for UUID

            // what happened
            $table->string('action', 100)->index(); // e.g. user.role.changed
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

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
