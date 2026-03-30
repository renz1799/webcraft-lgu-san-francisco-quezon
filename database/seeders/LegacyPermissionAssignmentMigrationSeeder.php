<?php

namespace Database\Seeders;

use App\Core\Models\Permission;
use Database\Seeders\Support\LegacyPermissionCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class LegacyPermissionAssignmentMigrationSeeder extends Seeder
{
    public function run(): void
    {
        $legacyPermissions = Permission::query()
            ->whereIn('name', LegacyPermissionCatalog::legacyNames())
            ->where('guard_name', 'web')
            ->orderBy('module_id')
            ->orderBy('name')
            ->get(['id', 'module_id', 'name', 'guard_name']);

        if ($legacyPermissions->isEmpty()) {
            $this->command?->info('No active legacy permission assignments found to migrate.');

            return;
        }

        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        $stats = [
            'migrated_permissions' => 0,
            'retired_permissions' => 0,
            'skipped_permissions' => 0,
            'role_assignments_created' => 0,
            'direct_assignments_created' => 0,
        ];

        DB::transaction(function () use (
            $legacyPermissions,
            $tableNames,
            $permissionPivotKey,
            $rolePivotKey,
            $modelKey,
            &$stats
        ): void {
            foreach ($legacyPermissions as $legacyPermission) {
                $targetNames = LegacyPermissionCatalog::normalizedNamesForLegacy($legacyPermission->name);

                if ($targetNames === []) {
                    $stats['skipped_permissions']++;

                    continue;
                }

                $targetPermissions = Permission::query()
                    ->where('module_id', $legacyPermission->module_id)
                    ->where('guard_name', $legacyPermission->guard_name)
                    ->whereIn('name', $targetNames)
                    ->get(['id', 'name']);

                $foundTargetNames = $targetPermissions->pluck('name')->all();
                $missingTargetNames = array_values(array_diff($targetNames, $foundTargetNames));

                if ($missingTargetNames !== []) {
                    $stats['skipped_permissions']++;
                    $this->command?->warn(sprintf(
                        'Skipped legacy permission [%s] in module [%s]; missing normalized targets: %s',
                        $legacyPermission->name,
                        $legacyPermission->module_id,
                        implode(', ', $missingTargetNames)
                    ));

                    continue;
                }

                $stats['role_assignments_created'] += $this->copyRoleAssignments(
                    $tableNames['role_has_permissions'],
                    $permissionPivotKey,
                    $rolePivotKey,
                    $legacyPermission->id,
                    $targetPermissions
                );

                $stats['direct_assignments_created'] += $this->copyDirectAssignments(
                    $tableNames['model_has_permissions'],
                    $permissionPivotKey,
                    $modelKey,
                    $legacyPermission->id,
                    $targetPermissions
                );

                DB::table($tableNames['role_has_permissions'])
                    ->where($permissionPivotKey, $legacyPermission->id)
                    ->delete();

                DB::table($tableNames['model_has_permissions'])
                    ->where($permissionPivotKey, $legacyPermission->id)
                    ->delete();

                $legacyPermission->delete();

                $stats['migrated_permissions']++;
                $stats['retired_permissions']++;
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info(sprintf(
            'Legacy permission assignment migration complete: %d permissions migrated, %d retired, %d skipped, %d role assignments copied, %d direct assignments copied.',
            $stats['migrated_permissions'],
            $stats['retired_permissions'],
            $stats['skipped_permissions'],
            $stats['role_assignments_created'],
            $stats['direct_assignments_created']
        ));
    }

    /**
     * @param  Collection<int, Permission>  $targetPermissions
     */
    private function copyRoleAssignments(
        string $table,
        string $permissionPivotKey,
        string $rolePivotKey,
        string $legacyPermissionId,
        Collection $targetPermissions
    ): int {
        $roleAssignments = DB::table($table)
            ->where($permissionPivotKey, $legacyPermissionId)
            ->get([$rolePivotKey]);

        if ($roleAssignments->isEmpty()) {
            return 0;
        }

        $inserted = 0;

        foreach ($targetPermissions as $targetPermission) {
            $rows = $roleAssignments->map(fn (object $assignment): array => [
                $permissionPivotKey => $targetPermission->id,
                $rolePivotKey => $assignment->{$rolePivotKey},
            ])->all();

            $inserted += DB::table($table)->insertOrIgnore($rows);
        }

        return $inserted;
    }

    /**
     * @param  Collection<int, Permission>  $targetPermissions
     */
    private function copyDirectAssignments(
        string $table,
        string $permissionPivotKey,
        string $modelKey,
        string $legacyPermissionId,
        Collection $targetPermissions
    ): int {
        $directAssignments = DB::table($table)
            ->where($permissionPivotKey, $legacyPermissionId)
            ->get(['module_id', 'model_type', $modelKey]);

        if ($directAssignments->isEmpty()) {
            return 0;
        }

        $inserted = 0;

        foreach ($targetPermissions as $targetPermission) {
            $rows = $directAssignments->map(fn (object $assignment): array => [
                $permissionPivotKey => $targetPermission->id,
                'module_id' => $assignment->module_id,
                'model_type' => $assignment->model_type,
                $modelKey => $assignment->{$modelKey},
            ])->all();

            $inserted += DB::table($table)->insertOrIgnore($rows);
        }

        return $inserted;
    }
}
