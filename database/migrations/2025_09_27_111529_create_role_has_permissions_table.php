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
        $pivotPermKey = $columnNames['permission_pivot_key'] ?? 'permission_id';

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRoleKey, $pivotPermKey) {
            $table->uuid($pivotPermKey);
            $table->uuid($pivotRoleKey);

            $table->foreign($pivotPermKey)
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRoleKey)
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermKey, $pivotRoleKey], 'role_has_permissions_permission_id_role_id_primary');
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['role_has_permissions']);
    }
};
