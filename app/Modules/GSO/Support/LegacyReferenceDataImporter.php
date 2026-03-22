<?php

namespace App\Modules\GSO\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class LegacyReferenceDataImporter
{
    /**
     * @var array<string, array{
     *     label: string,
     *     source_table?: string,
     *     target_table?: string,
     *     mode?: string,
     *     columns?: array<int, string>,
     *     dependencies: array<int, array{
     *         source_column: string,
     *         target_table: string,
     *         target_column?: string,
     *         label: string
     *     }>
     * }>
     */
    private const TABLES = [
        'asset_types' => [
            'label' => 'Asset Types',
            'mode' => 'passthrough',
            'columns' => ['id', 'type_code', 'type_name', 'created_at', 'updated_at', 'deleted_at'],
            'dependencies' => [],
        ],
        'asset_categories' => [
            'label' => 'Asset Categories',
            'mode' => 'passthrough',
            'columns' => ['id', 'asset_type_id', 'asset_code', 'asset_name', 'account_group', 'is_selected', 'created_at', 'updated_at', 'deleted_at'],
            'dependencies' => [[
                'source_column' => 'asset_type_id',
                'target_table' => 'asset_types',
                'target_column' => 'id',
                'label' => 'asset types',
            ]],
        ],
        'departments' => [
            'label' => 'Departments',
            'mode' => 'departments',
            'dependencies' => [],
        ],
        'fund_clusters' => [
            'label' => 'Fund Clusters',
            'mode' => 'passthrough',
            'columns' => ['id', 'code', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            'dependencies' => [],
        ],
        'fund_sources' => [
            'label' => 'Fund Sources',
            'mode' => 'passthrough',
            'columns' => ['id', 'fund_cluster_id', 'code', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            'dependencies' => [[
                'source_column' => 'fund_cluster_id',
                'target_table' => 'fund_clusters',
                'target_column' => 'id',
                'label' => 'fund clusters',
            ]],
        ],
        'accountable_officers' => [
            'label' => 'Accountable Officers',
            'mode' => 'accountable_officers',
            'dependencies' => [[
                'source_column' => 'department_id',
                'target_table' => 'departments',
                'target_column' => 'id',
                'label' => 'departments',
            ]],
        ],
        'airs' => [
            'label' => 'AIR Records',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'parent_air_id',
                'continuation_no',
                'po_number',
                'po_date',
                'air_number',
                'air_date',
                'invoice_number',
                'invoice_date',
                'supplier_name',
                'requesting_department_id',
                'requesting_department_name_snapshot',
                'requesting_department_code_snapshot',
                'fund_source_id',
                'fund',
                'status',
                'date_received',
                'received_completeness',
                'received_notes',
                'date_inspected',
                'inspection_verified',
                'inspection_notes',
                'inspected_by_name',
                'accepted_by_name',
                'created_by_user_id',
                'created_by_name_snapshot',
                'remarks',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'requesting_department_id',
                    'target_table' => 'departments',
                    'target_column' => 'id',
                    'label' => 'departments',
                ],
                [
                    'source_column' => 'fund_source_id',
                    'target_table' => 'fund_sources',
                    'target_column' => 'id',
                    'label' => 'fund sources',
                ],
            ],
        ],
        'items' => [
            'label' => 'Items',
            'mode' => 'passthrough',
            'columns' => ['id', 'asset_id', 'item_name', 'description', 'base_unit', 'item_identification', 'major_sub_account_group', 'tracking_type', 'requires_serial', 'is_semi_expendable', 'is_selected', 'created_at', 'updated_at', 'deleted_at'],
            'dependencies' => [[
                'source_column' => 'asset_id',
                'target_table' => 'asset_categories',
                'target_column' => 'id',
                'label' => 'asset categories',
            ]],
        ],
        'item_unit_conversions' => [
            'label' => 'Item Unit Conversions',
            'mode' => 'passthrough',
            'columns' => ['id', 'item_id', 'from_unit', 'multiplier', 'created_at', 'updated_at', 'deleted_at'],
            'dependencies' => [[
                'source_column' => 'item_id',
                'target_table' => 'items',
                'target_column' => 'id',
                'label' => 'items',
            ]],
        ],
        'item_component_templates' => [
            'label' => 'Item Component Templates',
            'mode' => 'passthrough',
            'columns' => ['id', 'item_id', 'line_no', 'name', 'quantity', 'unit', 'component_cost', 'remarks', 'created_at', 'updated_at', 'deleted_at'],
            'dependencies' => [[
                'source_column' => 'item_id',
                'target_table' => 'items',
                'target_column' => 'id',
                'label' => 'items',
            ]],
        ],
        'air_items' => [
            'label' => 'AIR Items',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'air_id',
                'item_id',
                'stock_no_snapshot',
                'item_name_snapshot',
                'description_snapshot',
                'unit_snapshot',
                'acquisition_cost',
                'qty_ordered',
                'qty_delivered',
                'qty_accepted',
                'tracking_type_snapshot',
                'requires_serial_snapshot',
                'is_semi_expendable_snapshot',
                'remarks',
                'created_at',
                'updated_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'air_id',
                    'target_table' => 'airs',
                    'target_column' => 'id',
                    'label' => 'air records',
                ],
                [
                    'source_column' => 'item_id',
                    'target_table' => 'items',
                    'target_column' => 'id',
                    'label' => 'items',
                ],
            ],
        ],
        'air_item_units' => [
            'label' => 'AIR Item Units',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'air_item_id',
                'inventory_item_id',
                'brand',
                'model',
                'serial_number',
                'property_number',
                'condition_status',
                'condition_notes',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'air_item_id',
                    'target_table' => 'air_items',
                    'target_column' => 'id',
                    'label' => 'air item rows',
                ],
                [
                    'source_column' => 'inventory_item_id',
                    'target_table' => 'inventory_items',
                    'target_column' => 'id',
                    'label' => 'inventory items',
                    'nullable' => true,
                ],
            ],
        ],
        'air_item_unit_files' => [
            'label' => 'AIR Item Unit Files',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'air_item_unit_id',
                'driver',
                'path',
                'type',
                'is_primary',
                'position',
                'original_name',
                'mime',
                'size',
                'caption',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [[
                'source_column' => 'air_item_unit_id',
                'target_table' => 'air_item_units',
                'target_column' => 'id',
                'label' => 'air item units',
            ]],
        ],
        'air_item_unit_components' => [
            'label' => 'AIR Item Unit Components',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'air_item_unit_id',
                'line_no',
                'name',
                'quantity',
                'unit',
                'component_cost',
                'serial_number',
                'condition',
                'is_present',
                'remarks',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [[
                'source_column' => 'air_item_unit_id',
                'target_table' => 'air_item_units',
                'target_column' => 'id',
                'label' => 'air item units',
            ]],
        ],
        'air_files' => [
            'label' => 'AIR Files',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'air_id',
                'driver',
                'path',
                'type',
                'is_primary',
                'position',
                'original_name',
                'mime',
                'size',
                'caption',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [[
                'source_column' => 'air_id',
                'target_table' => 'airs',
                'target_column' => 'id',
                'label' => 'air records',
            ]],
        ],
        'inventory_items' => [
            'label' => 'Inventory Items',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'item_id',
                'air_item_unit_id',
                'department_id',
                'fund_source_id',
                'property_number',
                'acquisition_date',
                'acquisition_cost',
                'description',
                'quantity',
                'unit',
                'stock_number',
                'service_life',
                'is_ics',
                'accountable_officer',
                'accountable_officer_id',
                'custody_state',
                'status',
                'condition',
                'brand',
                'model',
                'serial_number',
                'po_number',
                'drive_folder_id',
                'remarks',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'item_id',
                    'target_table' => 'items',
                    'target_column' => 'id',
                    'label' => 'items',
                ],
                [
                    'source_column' => 'department_id',
                    'target_table' => 'departments',
                    'target_column' => 'id',
                    'label' => 'departments',
                ],
                [
                    'source_column' => 'fund_source_id',
                    'target_table' => 'fund_sources',
                    'target_column' => 'id',
                    'label' => 'fund sources',
                ],
                [
                    'source_column' => 'accountable_officer_id',
                    'target_table' => 'accountable_officers',
                    'target_column' => 'id',
                    'label' => 'accountable officers',
                ],
            ],
        ],
        'inventory_item_components' => [
            'label' => 'Inventory Item Components',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'inventory_item_id',
                'line_no',
                'name',
                'quantity',
                'unit',
                'component_cost',
                'serial_number',
                'condition',
                'is_present',
                'remarks',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [[
                'source_column' => 'inventory_item_id',
                'target_table' => 'inventory_items',
                'target_column' => 'id',
                'label' => 'inventory items',
            ]],
        ],
        'inventory_item_files' => [
            'label' => 'Inventory Item Files',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'inventory_item_id',
                'driver',
                'path',
                'type',
                'is_primary',
                'position',
                'original_name',
                'mime',
                'size',
                'caption',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [[
                'source_column' => 'inventory_item_id',
                'target_table' => 'inventory_items',
                'target_column' => 'id',
                'label' => 'inventory items',
            ]],
        ],
        'inventory_item_events' => [
            'label' => 'Inventory Item Events',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'inventory_item_id',
                'department_id',
                'performed_by_user_id',
                'event_type',
                'event_date',
                'qty_in',
                'qty_out',
                'amount_snapshot',
                'unit_snapshot',
                'office_snapshot',
                'officer_snapshot',
                'status',
                'condition',
                'person_accountable',
                'notes',
                'reference_type',
                'reference_no',
                'reference_id',
                'fund_source_id',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'inventory_item_id',
                    'target_table' => 'inventory_items',
                    'target_column' => 'id',
                    'label' => 'inventory items',
                ],
                [
                    'source_column' => 'department_id',
                    'target_table' => 'departments',
                    'target_column' => 'id',
                    'label' => 'departments',
                ],
                [
                    'source_column' => 'performed_by_user_id',
                    'target_table' => 'users',
                    'target_column' => 'id',
                    'label' => 'users',
                ],
                [
                    'source_column' => 'fund_source_id',
                    'target_table' => 'fund_sources',
                    'target_column' => 'id',
                    'label' => 'fund sources',
                ],
            ],
        ],
        'inventory_item_event_files' => [
            'label' => 'Inventory Item Event Files',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'inventory_item_event_id',
                'disk',
                'path',
                'drive_file_id',
                'drive_web_view_link',
                'original_name',
                'mime_type',
                'size_bytes',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [[
                'source_column' => 'inventory_item_event_id',
                'target_table' => 'inventory_item_events',
                'target_column' => 'id',
                'label' => 'inventory item events',
            ]],
        ],
        'inspections' => [
            'label' => 'Inspections',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'inspector_user_id',
                'reviewer_user_id',
                'status',
                'office_department',
                'accountable_officer',
                'dv_number',
                'po_number',
                'observed_description',
                'item_name',
                'brand',
                'model',
                'serial_number',
                'acquisition_cost',
                'acquisition_date',
                'department_id',
                'item_id',
                'quantity',
                'condition',
                'drive_folder_id',
                'remarks',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'inspector_user_id',
                    'target_table' => 'users',
                    'target_column' => 'id',
                    'label' => 'users',
                ],
                [
                    'source_column' => 'reviewer_user_id',
                    'target_table' => 'users',
                    'target_column' => 'id',
                    'label' => 'users',
                ],
                [
                    'source_column' => 'department_id',
                    'target_table' => 'departments',
                    'target_column' => 'id',
                    'label' => 'departments',
                ],
                [
                    'source_column' => 'item_id',
                    'target_table' => 'items',
                    'target_column' => 'id',
                    'label' => 'items',
                ],
            ],
        ],
        'inspection_photos' => [
            'label' => 'Inspection Photos',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'inspection_id',
                'driver',
                'path',
                'original_name',
                'mime',
                'size',
                'caption',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [[
                'source_column' => 'inspection_id',
                'target_table' => 'inspections',
                'target_column' => 'id',
                'label' => 'inspections',
            ]],
        ],
        'stocks' => [
            'label' => 'Stocks',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'item_id',
                'fund_source_id',
                'on_hand',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'item_id',
                    'target_table' => 'items',
                    'target_column' => 'id',
                    'label' => 'items',
                ],
                [
                    'source_column' => 'fund_source_id',
                    'target_table' => 'fund_sources',
                    'target_column' => 'id',
                    'label' => 'fund sources',
                ],
            ],
        ],
        'stock_movements' => [
            'label' => 'Stock Movements',
            'mode' => 'passthrough',
            'columns' => [
                'id',
                'item_id',
                'fund_source_id',
                'movement_type',
                'qty',
                'reference_type',
                'reference_id',
                'air_item_id',
                'ris_item_id',
                'occurred_at',
                'created_by_name',
                'remarks',
                'created_at',
                'updated_at',
                'deleted_at',
            ],
            'dependencies' => [
                [
                    'source_column' => 'item_id',
                    'target_table' => 'items',
                    'target_column' => 'id',
                    'label' => 'items',
                ],
                [
                    'source_column' => 'fund_source_id',
                    'target_table' => 'fund_sources',
                    'target_column' => 'id',
                    'label' => 'fund sources',
                ],
            ],
        ],
    ];

    public function import(array $onlyTables = [], bool $dryRun = false): array
    {
        $legacyConnectionName = (string) config('gso.legacy.connection', 'gso_legacy');
        $legacyConnection = DB::connection($legacyConnectionName);
        $targetConnection = DB::connection();
        $selectedTables = $this->resolveSelectedTables($onlyTables);

        try {
            $legacyConnection->getPdo();
            $targetConnection->getPdo();
        } catch (Throwable $exception) {
            throw new RuntimeException('Could not connect to the legacy or target database: ' . $exception->getMessage(), 0, $exception);
        }

        $reportTables = [];

        $targetConnection->beginTransaction();

        try {
            foreach ($selectedTables as $tableName => $tableConfig) {
                $reportTables[] = $this->importTable(
                    legacyConnection: $legacyConnection,
                    targetConnection: $targetConnection,
                    tableName: $tableName,
                    tableConfig: $tableConfig,
                    dryRun: $dryRun,
                );
            }

            if ($dryRun) {
                $targetConnection->rollBack();
            } else {
                $targetConnection->commit();
            }
        } catch (Throwable $exception) {
            if ($targetConnection->transactionLevel() > 0) {
                $targetConnection->rollBack();
            }

            throw $exception;
        }

        return [
            'connection' => $legacyConnectionName,
            'dry_run' => $dryRun,
            'tables' => $reportTables,
            'summary' => [
                'connection' => $legacyConnectionName,
                'dry_run' => $dryRun,
                'selected_tables' => count($reportTables),
                'total_rows_read' => (int) collect($reportTables)->sum('rows_read'),
                'total_rows_written' => $dryRun ? 0 : (int) collect($reportTables)->sum('rows_written'),
            ],
        ];
    }

    /**
     * @param  array{label: string, columns: array<int, string>, dependencies: array<int, string>}  $tableConfig
     * @return array<string, mixed>
     */
    private function importTable(
        ConnectionInterface $legacyConnection,
        ConnectionInterface $targetConnection,
        string $tableName,
        array $tableConfig,
        bool $dryRun,
    ): array {
        $sourceTable = (string) ($tableConfig['source_table'] ?? $tableName);
        $targetTable = (string) ($tableConfig['target_table'] ?? $tableName);
        $legacySchema = $legacyConnection->getSchemaBuilder();
        $targetSchema = $targetConnection->getSchemaBuilder();

        if (! $legacySchema->hasTable($sourceTable)) {
            throw new RuntimeException("Legacy table [{$sourceTable}] does not exist on connection [{$legacyConnection->getName()}].");
        }

        if (! $targetSchema->hasTable($targetTable)) {
            throw new RuntimeException("Target table [{$targetTable}] does not exist on connection [{$targetConnection->getName()}].");
        }

        $this->assertDependenciesSatisfied(
            legacyConnection: $legacyConnection,
            targetConnection: $targetConnection,
            tableName: $tableName,
            tableConfig: $tableConfig,
            sourceTable: $sourceTable,
        );

        [$rows, $updateColumns, $importColumns] = $this->buildRowsForImport(
            legacyConnection: $legacyConnection,
            targetConnection: $targetConnection,
            tableName: $tableName,
            tableConfig: $tableConfig,
            sourceTable: $sourceTable,
            targetTable: $targetTable,
        );

        if ($rows !== []) {
            $targetConnection->table($targetTable)->upsert($rows, ['id'], $updateColumns);
        }

        return [
            'table' => $tableName,
            'label' => $tableConfig['label'],
            'source_table' => $sourceTable,
            'target_table' => $targetTable,
            'rows_read' => count($rows),
            'rows_written' => $dryRun ? 0 : count($rows),
            'status' => $dryRun ? 'dry-run' : 'imported',
            'columns' => $importColumns,
        ];
    }

    /**
     * @param  array{
     *     label: string,
     *     source_table?: string,
     *     target_table?: string,
     *     mode?: string,
     *     columns?: array<int, string>,
     *     dependencies: array<int, array{
     *         source_column: string,
     *         target_table: string,
     *         target_column?: string,
     *         label: string
     *     }>
     * }  $tableConfig
     */
    private function assertDependenciesSatisfied(
        ConnectionInterface $legacyConnection,
        ConnectionInterface $targetConnection,
        string $tableName,
        array $tableConfig,
        string $sourceTable,
    ): void {
        if ($tableConfig['dependencies'] === []) {
            return;
        }

        $legacyColumns = $legacyConnection->getSchemaBuilder()->getColumnListing($sourceTable);

        foreach ($tableConfig['dependencies'] as $dependency) {
            $sourceColumn = (string) ($dependency['source_column'] ?? '');
            $targetTable = (string) ($dependency['target_table'] ?? '');
            $targetColumn = (string) ($dependency['target_column'] ?? 'id');
            $label = (string) ($dependency['label'] ?? $targetTable);

            if ($sourceColumn === '' || $targetTable === '' || ! in_array($sourceColumn, $legacyColumns, true)) {
                continue;
            }

            if (! $targetConnection->getSchemaBuilder()->hasTable($targetTable)) {
                throw new RuntimeException("Target dependency table [{$targetTable}] is missing while importing [{$tableName}].");
            }

            $missingIds = $legacyConnection->table($sourceTable)
                ->distinct()
                ->pluck($sourceColumn)
                ->filter(fn (mixed $value): bool => trim((string) $value) !== '')
                ->diff(
                    $targetConnection->table($targetTable)
                        ->pluck($targetColumn)
                        ->all()
                )
                ->values();

            if ($missingIds->isEmpty()) {
                continue;
            }

            $samples = $missingIds->take(5)->implode(', ');

            throw new RuntimeException(
                "Cannot import [{$tableName}] because referenced {$label} are missing from the target table. "
                . 'Examples: ' . $samples
            );
        }
    }

    /**
     * @param  array{
     *     label: string,
     *     source_table?: string,
     *     target_table?: string,
     *     mode?: string,
     *     columns?: array<int, string>,
     *     dependencies: array<int, array{
     *         source_column: string,
     *         target_table: string,
     *         target_column?: string,
     *         label: string
     *     }>
     * }  $tableConfig
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>, 2: array<int, string>}
     */
    private function buildRowsForImport(
        ConnectionInterface $legacyConnection,
        ConnectionInterface $targetConnection,
        string $tableName,
        array $tableConfig,
        string $sourceTable,
        string $targetTable,
    ): array {
        return match ((string) ($tableConfig['mode'] ?? 'passthrough')) {
            'departments' => $this->buildDepartmentRows($legacyConnection, $targetConnection, $sourceTable, $targetTable),
            'accountable_officers' => $this->buildAccountableOfficerRows($legacyConnection, $targetConnection, $sourceTable, $targetTable),
            default => $this->buildPassthroughRows($legacyConnection, $targetConnection, $tableName, $tableConfig, $sourceTable, $targetTable),
        };
    }

    /**
     * @param  array{
     *     label: string,
     *     source_table?: string,
     *     target_table?: string,
     *     mode?: string,
     *     columns?: array<int, string>,
     *     dependencies: array<int, array{
     *         source_column: string,
     *         target_table: string,
     *         target_column?: string,
     *         label: string
     *     }>
     * }  $tableConfig
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>, 2: array<int, string>}
     */
    private function buildPassthroughRows(
        ConnectionInterface $legacyConnection,
        ConnectionInterface $targetConnection,
        string $tableName,
        array $tableConfig,
        string $sourceTable,
        string $targetTable,
    ): array {
        $legacyColumns = $legacyConnection->getSchemaBuilder()->getColumnListing($sourceTable);
        $targetColumns = $targetConnection->getSchemaBuilder()->getColumnListing($targetTable);
        $importColumns = array_values(array_intersect((array) ($tableConfig['columns'] ?? []), $legacyColumns, $targetColumns));

        if (! in_array('id', $importColumns, true)) {
            throw new RuntimeException("Cannot import [{$tableName}] because the shared id column is missing.");
        }

        $rows = $legacyConnection->table($sourceTable)
            ->orderBy('id')
            ->get($importColumns)
            ->map(function (object $row) use ($tableName): array {
                $data = (array) $row;

                if ($tableName === 'accountable_officers') {
                    $data['normalized_name'] = $this->normalizeNullableString(
                        $data['normalized_name'] ?? null
                    ) ?? $this->normalizeName((string) ($data['full_name'] ?? ''));
                }

                return $data;
            })
            ->all();

        return [
            $rows,
            array_values(array_diff($importColumns, ['id'])),
            $importColumns,
        ];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>, 2: array<int, string>}
     */
    private function buildDepartmentRows(
        ConnectionInterface $legacyConnection,
        ConnectionInterface $targetConnection,
        string $sourceTable,
        string $targetTable,
    ): array {
        $legacyColumns = $legacyConnection->getSchemaBuilder()->getColumnListing($sourceTable);
        $targetColumns = $targetConnection->getSchemaBuilder()->getColumnListing($targetTable);
        $selectColumns = array_values(array_intersect(
            ['id', 'department_code', 'department_name', 'department_abbr', 'created_at', 'updated_at', 'deleted_at'],
            $legacyColumns
        ));

        foreach (['id', 'department_code', 'department_name'] as $requiredColumn) {
            if (! in_array($requiredColumn, $selectColumns, true)) {
                throw new RuntimeException("Cannot import [departments] because required legacy column [{$requiredColumn}] is missing.");
            }
        }

        $rows = $legacyConnection->table($sourceTable)
            ->orderBy('id')
            ->get($selectColumns)
            ->map(function (object $row) use ($targetColumns): array {
                $source = (array) $row;
                $deletedAt = $source['deleted_at'] ?? null;
                $mapped = [
                    'id' => (string) $source['id'],
                    'code' => $this->normalizeString((string) ($source['department_code'] ?? '')),
                    'name' => $this->normalizeString((string) ($source['department_name'] ?? '')),
                    'short_name' => $this->normalizeNullableString($source['department_abbr'] ?? null),
                    'type' => 'office',
                    'is_active' => $deletedAt === null,
                    'created_at' => $source['created_at'] ?? null,
                    'updated_at' => $source['updated_at'] ?? null,
                    'deleted_at' => $deletedAt,
                ];

                return $this->onlyTargetColumns($mapped, $targetColumns);
            })
            ->all();

        $importColumns = $this->resolveImportedColumnsFromRows(
            $rows,
            ['id', 'code', 'name', 'short_name', 'type', 'is_active', 'created_at', 'updated_at', 'deleted_at']
        );

        $updateColumns = array_values(array_intersect(
            ['code', 'name', 'short_name', 'created_at', 'updated_at', 'deleted_at'],
            $importColumns
        ));

        return [$rows, $updateColumns, $importColumns];
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: array<int, string>, 2: array<int, string>}
     */
    private function buildAccountableOfficerRows(
        ConnectionInterface $legacyConnection,
        ConnectionInterface $targetConnection,
        string $sourceTable,
        string $targetTable,
    ): array {
        $legacyColumns = $legacyConnection->getSchemaBuilder()->getColumnListing($sourceTable);
        $targetColumns = $targetConnection->getSchemaBuilder()->getColumnListing($targetTable);
        $selectColumns = array_values(array_intersect(
            ['id', 'full_name', 'normalized_name', 'designation', 'office', 'department_id', 'is_active', 'created_at', 'updated_at', 'deleted_at'],
            $legacyColumns
        ));

        foreach (['id', 'full_name'] as $requiredColumn) {
            if (! in_array($requiredColumn, $selectColumns, true)) {
                throw new RuntimeException("Cannot import [accountable_officers] because required legacy column [{$requiredColumn}] is missing.");
            }
        }

        $rows = $legacyConnection->table($sourceTable)
            ->orderBy('id')
            ->get($selectColumns)
            ->map(function (object $row) use ($targetColumns): array {
                $source = (array) $row;
                $fullName = $this->normalizeString((string) ($source['full_name'] ?? ''));
                $mapped = [
                    'id' => (string) $source['id'],
                    'full_name' => $fullName,
                    'normalized_name' => $this->normalizeNullableString($source['normalized_name'] ?? null)
                        ?? $this->normalizeName($fullName),
                    'designation' => $this->normalizeNullableString($source['designation'] ?? null),
                    'office' => $this->normalizeNullableString($source['office'] ?? null),
                    'department_id' => $this->normalizeNullableString($source['department_id'] ?? null),
                    'is_active' => array_key_exists('is_active', $source)
                        ? (bool) $source['is_active']
                        : (($source['deleted_at'] ?? null) === null),
                    'created_at' => $source['created_at'] ?? null,
                    'updated_at' => $source['updated_at'] ?? null,
                    'deleted_at' => $source['deleted_at'] ?? null,
                ];

                return $this->onlyTargetColumns($mapped, $targetColumns);
            })
            ->all();

        $importColumns = $this->resolveImportedColumnsFromRows(
            $rows,
            ['id', 'full_name', 'normalized_name', 'designation', 'office', 'department_id', 'is_active', 'created_at', 'updated_at', 'deleted_at']
        );

        return [
            $rows,
            array_values(array_diff($importColumns, ['id'])),
            $importColumns,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $targetColumns
     * @return array<string, mixed>
     */
    private function onlyTargetColumns(array $data, array $targetColumns): array
    {
        return array_intersect_key($data, array_flip($targetColumns));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $fallbackColumns
     * @return array<int, string>
     */
    private function resolveImportedColumnsFromRows(array $rows, array $fallbackColumns): array
    {
        if ($rows === []) {
            return $fallbackColumns;
        }

        return array_values(array_keys($rows[0]));
    }

    private function normalizeString(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = $this->normalizeString((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    private function normalizeName(string $value): string
    {
        return mb_strtolower($this->normalizeString($value));
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     source_table?: string,
     *     target_table?: string,
     *     mode?: string,
     *     columns?: array<int, string>,
     *     dependencies: array<int, array{
     *         source_column: string,
     *         target_table: string,
     *         target_column?: string,
     *         label: string
     *     }>
     * }>
     */
    private function resolveSelectedTables(array $onlyTables): array
    {
        $lookup = collect($onlyTables)
            ->map(fn (mixed $table) => Str::lower(trim((string) $table)))
            ->filter()
            ->values();

        if ($lookup->isEmpty()) {
            return self::TABLES;
        }

        $unknownTables = $lookup
            ->reject(fn (string $table) => array_key_exists($table, self::TABLES))
            ->values();

        if ($unknownTables->isNotEmpty()) {
            throw new InvalidArgumentException(
                'Unsupported legacy reference import table(s): ' . $unknownTables->implode(', ')
            );
        }

        return collect(self::TABLES)
            ->filter(fn (array $config, string $table) => $lookup->contains($table))
            ->all();
    }
}
