<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableNames   = config('permission.table_names');
        $columnNames  = config('permission.column_names');
        $pivotRoleKey = $columnNames['role_pivot_key'] ?? 'role_id';

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRoleKey) {
            $table->uuid('module_id');
            $table->uuid($pivotRoleKey);

            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);

            $table->index('module_id', 'model_has_roles_module_id_index');
            $table->index(
                [$columnNames['model_morph_key'], 'model_type', 'module_id'],
                'model_has_roles_model_id_type_module_index'
            );

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('cascade');

            $table->foreign($pivotRoleKey)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(
                ['module_id', $pivotRoleKey, $columnNames['model_morph_key'], 'model_type'],
                'model_has_roles_module_role_model_type_primary'
            );
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['model_has_roles']);
    }
};