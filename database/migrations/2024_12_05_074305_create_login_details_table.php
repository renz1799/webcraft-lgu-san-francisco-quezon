<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginDetailsTable extends Migration
{
    public function up(): void
    {
        Schema::create('login_details', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Nullable so logs remain even if user is deleted/absent
            $table->uuid('user_id')->nullable();
            $table->uuid('module_id')->nullable();
            $table->string('email')->nullable();          // capture attempted email (even if user_id is null)
            $table->boolean('success')->default(false);   // success or failure
            $table->string('reason', 32)->nullable();     // e.g. ok, unknown_email, invalid_password, inactive, guard_reject

            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('device')->nullable();           // user-agent can be long
            $table->string('location')->nullable();       // Google Maps URL
            $table->string('address')->nullable();        // human-readable address
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();

            // FK kept nullable to preserve log rows; null user_id if user is deleted
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->nullOnDelete();

            // Indexes for performance & filtering
            $table->index('user_id');
            $table->index('module_id');
            $table->index('email');
            $table->index('ip_address');
            $table->index('success');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_details');
    }
}
