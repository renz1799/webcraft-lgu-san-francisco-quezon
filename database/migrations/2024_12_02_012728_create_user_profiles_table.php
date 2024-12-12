<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID for the profile
            $table->uuid('user_id'); // Foreign key to users table
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('name_extension')->nullable(); // e.g., Jr., Sr.
            $table->text('address')->nullable();
            $table->string('contact_details')->nullable(); // Phone, etc.
            $table->string('profile_photo_path')->nullable(); // Add profile photo path
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Cascade delete
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}

