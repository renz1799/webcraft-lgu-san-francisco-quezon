<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('login_details', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Unique ID
            $table->uuid('user_id'); // Foreign key to users table
            $table->string('ip_address')->nullable(); // IP address of the user
            $table->string('device')->nullable(); // User device details
            $table->string('location')->nullable(); // Google Maps link for the location
            $table->timestamps(); // Laravel default timestamps

            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_details');
    }
}

