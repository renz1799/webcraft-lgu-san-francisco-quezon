<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUsersTableForUuidAndPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraints if the `id` column is referenced in other tables
            // Schema::disableForeignKeyConstraints();

            // Change the `id` column to remove auto-increment and the primary key
            $table->dropColumn('id');

            // Add a new `id` column as UUID
            $table->uuid('id')->primary()->first();

            // Drop the `name` column
            $table->dropColumn('name');

            // Add `username` column (unique)
            $table->string('username')->unique()->after('id');

            // Add `user_type` column with a default value
            $table->string('user_type')->default('Viewer')->after('username');

            // Schema::enableForeignKeyConstraints();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop UUID `id` column
            $table->dropColumn('id');

            // Restore the auto-incrementing `id` column
            $table->id()->first();

            // Restore the `name` column
            $table->string('name')->after('id');

            // Drop `username` and `user_type` columns
            $table->dropColumn(['username', 'user_type']);
        });
    }
}
