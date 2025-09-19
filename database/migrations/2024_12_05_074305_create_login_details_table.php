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

            $table->uuid('user_id');
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('device')->nullable();           // user-agent can be long
            $table->string('location')->nullable();       // Google Maps URL
            $table->string('address')->nullable();        // human-readable address
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // Indexes for performance
            $table->index('user_id');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_details');
    }
}
