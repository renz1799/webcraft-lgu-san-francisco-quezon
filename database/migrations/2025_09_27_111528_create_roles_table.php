<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $teams       = config('permission.teams');
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->uuid('id')->primary();

            if ($teams || config('permission.testing')) {
                $table->uuid($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }

            $table->string('name');
            $table->string('guard_name');

            $table->timestamps();
            $table->softDeletes();

            if ($teams || config('permission.testing')) {
                $table->unique(
                    [$columnNames['team_foreign_key'], 'name', 'guard_name', 'deleted_at'],
                    'roles_team_name_guard_deleted_unique'
                );
            } else {
                $table->unique(['name', 'guard_name', 'deleted_at'], 'roles_name_guard_deleted_unique');
            }
        });
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        Schema::dropIfExists($tableNames['roles']);
    }
};
