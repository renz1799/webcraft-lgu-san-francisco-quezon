<?php

namespace Database\Seeders\Support;

class LegacyPermissionCatalog
{
    /**
     * @var array<int, array<int, string>>
     */
    private const GROUPS = [
        ['tasks.view', 'view Tasks'],
        ['tasks.view_all', 'view All Tasks'],
        ['tasks.reassign', 'modify Reassign Tasks'],
        ['audit_logs.view', 'view Audit Logs'],
        ['audit_logs.restore_data', 'modify Allow Data Restoration'],
        ['login_logs.view', 'view Login Logs'],
        ['drive_connections.view', 'modify Google Drive Connection'],
        ['drive_connections.connect', 'modify Google Drive Connection'],
        ['drive_connections.disconnect', 'modify Google Drive Connection'],
        ['drive_files.create', 'create Drive Files'],
        ['asset_types.view', 'view Asset Types'],
        ['asset_types.create', 'asset_types.update', 'asset_types.archive', 'asset_types.restore', 'modify Asset Types'],
        ['asset_categories.view', 'view Asset Categories'],
        ['asset_categories.create', 'asset_categories.update', 'asset_categories.archive', 'asset_categories.restore', 'modify Asset Categories'],
        ['departments.view', 'view Departments'],
        ['departments.create', 'departments.update', 'departments.archive', 'departments.restore', 'modify Departments'],
        ['fund_clusters.view', 'view Fund Clusters'],
        ['fund_clusters.create', 'fund_clusters.update', 'fund_clusters.archive', 'fund_clusters.restore', 'modify Fund Clusters'],
        ['fund_sources.view', 'view Fund Sources'],
        ['fund_sources.create', 'fund_sources.update', 'fund_sources.archive', 'fund_sources.restore', 'modify Fund Sources'],
        ['accountable_persons.view', 'view Accountable Persons', 'view Accountable Officers'],
        ['accountable_persons.create', 'accountable_persons.update', 'accountable_persons.archive', 'accountable_persons.restore', 'modify Accountable Persons', 'modify Accountable Officers'],
        ['items.view', 'view Items'],
        ['items.create', 'items.update', 'items.archive', 'items.restore', 'modify Items'],
        ['inventory_items.view', 'view Inventory Items'],
        ['inventory_items.create', 'inventory_items.update', 'inventory_items.archive', 'inventory_items.restore', 'inventory_items.manage_files', 'inventory_items.manage_events', 'inventory_items.import_from_inspection', 'modify Inventory Items'],
        ['inspections.view', 'view Inspections'],
        ['inspections.create', 'inspections.update', 'inspections.archive', 'inspections.restore', 'inspections.manage_photos', 'modify Inspections'],
        ['stocks.view', 'view Stocks'],
        ['stocks.adjust', 'stocks.view_ledger', 'modify Stocks'],
        ['air.view', 'view AIR'],
        ['air.create', 'create AIR'],
        ['air.update', 'air.inspect', 'air.manage_items', 'air.manage_files', 'air.promote_inventory', 'air.finalize_inspection', 'air.archive', 'air.print', 'modify AIR'],
        ['air.reopen_inspection', 'modify AIR', 'modify Inspection Status'],
        ['air.restore', 'modify AIR'],
        ['ris.view', 'view RIS'],
        ['ris.create', 'create RIS'],
        ['ris.update', 'ris.manage_items', 'ris.generate_from_air', 'ris.print', 'modify RIS'],
        ['ris.submit', 'submit RIS', 'modify RIS'],
        ['ris.approve', 'approve RIS'],
        ['ris.reject', 'reject RIS'],
        ['ris.reopen', 'reopen RIS'],
        ['ris.revert', 'revert RIS'],
        ['ris.archive', 'delete RIS'],
        ['ris.restore', 'restore RIS', 'modify Allow Data Restoration'],
        ['par.view', 'view PAR'],
        ['par.create', 'par.update', 'par.submit', 'par.finalize', 'par.reopen', 'par.archive', 'par.manage_items', 'par.print', 'modify PAR'],
        ['par.restore', 'restore PAR', 'modify Allow Data Restoration'],
        ['ics.view', 'view ICS'],
        ['ics.create', 'ics.update', 'ics.submit', 'ics.finalize', 'ics.reopen', 'ics.archive', 'ics.manage_items', 'ics.print', 'modify ICS'],
        ['ics.restore', 'restore ICS', 'modify Allow Data Restoration'],
        ['ptr.view', 'view PTR'],
        ['ptr.create', 'ptr.update', 'ptr.submit', 'ptr.finalize', 'ptr.reopen', 'ptr.archive', 'ptr.manage_items', 'ptr.print', 'modify PTR'],
        ['ptr.restore', 'restore PTR', 'modify Allow Data Restoration'],
        ['itr.view', 'view ITR'],
        ['itr.create', 'itr.update', 'itr.submit', 'itr.finalize', 'itr.reopen', 'itr.archive', 'itr.manage_items', 'itr.print', 'modify ITR'],
        ['itr.restore', 'restore ITR', 'modify Allow Data Restoration'],
        ['wmr.view', 'view WMR'],
        ['wmr.create', 'wmr.update', 'wmr.submit', 'wmr.approve', 'wmr.finalize', 'wmr.reopen', 'wmr.archive', 'wmr.manage_items', 'wmr.print', 'modify WMR'],
        ['wmr.restore', 'restore WMR', 'modify Allow Data Restoration'],
    ];

    /**
     * @return array<int, string>
     */
    public static function legacyNames(): array
    {
        $legacyNames = [];

        foreach (self::GROUPS as $group) {
            foreach ($group as $permission) {
                if (self::isLegacyName($permission)) {
                    $legacyNames[] = $permission;
                }
            }
        }

        return array_values(array_unique($legacyNames));
    }

    /**
     * @return array<int, string>
     */
    public static function normalizedNamesForLegacy(string $legacyPermission): array
    {
        $normalized = trim($legacyPermission);

        if ($normalized === '' || self::isNormalizedKey($normalized)) {
            return [];
        }

        $targetNames = [];

        foreach (self::GROUPS as $group) {
            if (! in_array($normalized, $group, true)) {
                continue;
            }

            foreach ($group as $permission) {
                if (self::isNormalizedKey($permission)) {
                    $targetNames[] = $permission;
                }
            }
        }

        return array_values(array_unique($targetNames));
    }

    private static function isLegacyName(string $permission): bool
    {
        $normalized = trim($permission);

        return $normalized !== '' && ! self::isNormalizedKey($normalized);
    }

    private static function isNormalizedKey(string $permission): bool
    {
        return preg_match('/^[a-z0-9_]+(?:\.[a-z0-9_]+)+$/', trim($permission)) === 1;
    }
}
