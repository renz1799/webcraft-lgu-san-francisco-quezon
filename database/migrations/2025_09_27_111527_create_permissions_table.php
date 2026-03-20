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

            $table->uuid('module_id');
            $table->string('name');
            $table->string('page')->nullable();
            $table->string('guard_name');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('cascade');

            $table->unique(
                ['module_id', 'name', 'guard_name', 'deleted_at'],
                'permissions_module_name_guard_deleted_unique'
            );

            $table->index('module_id', 'permissions_module_id_index');
            $table->index('page', 'permissions_page_index');
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['permissions']);
    }
};