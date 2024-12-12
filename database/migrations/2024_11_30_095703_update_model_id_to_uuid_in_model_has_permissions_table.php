<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateModelIdToUuidInModelHasPermissionsTable extends Migration
{
    public function up()
    {
        Schema::table('model_has_permissions', function (Blueprint $table) {
            // Change `model_id` column to UUID (string)
            $table->uuid('model_id')->change();
        });
    }

    public function down()
    {
        Schema::table('model_has_permissions', function (Blueprint $table) {
            // Revert `model_id` column back to integer (if needed)
            $table->unsignedBigInteger('model_id')->change();
        });
    }
}
