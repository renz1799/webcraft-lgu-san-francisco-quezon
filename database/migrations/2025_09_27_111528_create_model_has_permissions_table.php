<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableNames   = config('permission.table_names');
        $columnNames  = config('permission.column_names');
        $pivotPermKey = $columnNames['permission_pivot_key'] ?? 'permission_id';

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermKey) {
            $table->uuid('module_id');
            $table->uuid($pivotPermKey);

            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);

            $table->index('module_id', 'model_has_permissions_module_id_index');
            $table->index(
                [$columnNames['model_morph_key'], 'model_type', 'module_id'],
                'model_has_permissions_model_id_type_module_index'
            );

            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->onDelete('cascade');

            $table->foreign($pivotPermKey)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->primary(
                ['module_id', $pivotPermKey, $columnNames['model_morph_key'], 'model_type'],
                'model_has_permissions_module_permission_model_type_primary'
            );
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['model_has_permissions']);
    }
};