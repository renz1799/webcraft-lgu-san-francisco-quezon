<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        Schema::create($tableNames['roles'], function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('module_id');
            $table->string('name');
            $table->string('guard_name');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('cascade');

            $table->unique(
                ['module_id', 'name', 'guard_name'],
                'roles_module_name_guard_unique'
            );

            $table->index('module_id', 'roles_module_id_index');
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['roles']);
    }
};
