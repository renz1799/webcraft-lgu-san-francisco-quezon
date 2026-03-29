<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_identity_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');

            $table->string('current_first_name')->nullable();
            $table->string('current_last_name')->nullable();
            $table->string('current_middle_name')->nullable();
            $table->string('current_name_extension', 50)->nullable();

            $table->string('requested_first_name');
            $table->string('requested_last_name');
            $table->string('requested_middle_name')->nullable();
            $table->string('requested_name_extension', 50)->nullable();

            $table->text('reason')->nullable();
            $table->string('status', 30)->default('pending');
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['user_id', 'status'], 'identity_change_requests_user_status_idx');
            $table->index(['status', 'created_at'], 'identity_change_requests_status_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_identity_change_requests');
    }
};
