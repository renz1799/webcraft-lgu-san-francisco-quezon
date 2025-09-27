<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->uuid('id')->primary();

            // your business fields
            $table->string('name');
            $table->string('page')->nullable();       // <— merged “add_page” here
            $table->string('guard_name');

            $table->timestamps();
            $table->softDeletes();

            // allow re-using names after a soft delete (NULL != NULL trick)
            $table->unique(['name', 'guard_name', 'deleted_at'], 'permissions_name_guard_deleted_unique');
            $table->index('page');
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['permissions']);
    }
};
