<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyModelIdInModelHasRolesTable extends Migration
{
    public function up()
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->uuid('model_id')->change(); // Change `model_id` to UUID (string)
        });
    }

    public function down()
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change(); // Revert back to integer if needed
        });
    }
}
