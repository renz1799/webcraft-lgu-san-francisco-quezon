<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLoginDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('login_details', function (Blueprint $table) {
            // Add new columns
            $table->string('address')->nullable()->after('location'); // For City, Province, Country
        });
    }
    
    public function down()
    {
        Schema::table('login_details', function (Blueprint $table) {
            // Drop newly added columns
            $table->dropColumn(['address']);
        });
    }
    
}
