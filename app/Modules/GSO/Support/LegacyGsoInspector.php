<?php

namespace App\Modules\GSO\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class LegacyGsoInspector
{
    public function inspect(string|int|null $wave = null, array $onlyTables = []): array
    {
        $connection = (string) config('gso.legacy.connection', 'gso_legacy');
        $referencePath = (string) config('gso.legacy.reference_path', '');
        $selectedWaves = $this->selectedWaves($wave);
        $onlyLookup = collect($onlyTables)
            ->map(fn (mixed $table) => Str::lower(trim((string) $table)))
            ->filter()
            ->values()
            ->all();

        $connectionReachable = true;
        $connectionError = null;
        $schema = null;

        try {
            $db = DB::connection($connection);
            $db->getPdo();
            $schema = $db->getSchemaBuilder();
        } catch (Throwable $exception) {
            $connectionReachable = false;
            $connectionError = $exception->getMessage();
        }

        $tables = [];

        foreach ($selectedWaves as $waveNumber => $waveConfig) {
            foreach ((array) data_get($waveConfig, 'tables', []) as $tableConfig) {
                $tableName = (string) data_get($tableConfig, 'table');

                if ($tableName === '' || ! $this->matchesOnlyFilter($tableName, $onlyLookup)) {
                    continue;
                }

                $tables[] = $this->inspectTable(
                    connection: $connection,
                    schema: $schema,
                    connectionReachable: $connectionReachable,
                    connectionError: $connectionError,
                    tableName: $tableName,
                    label: (string) data_get($tableConfig, 'label', Str::headline(str_replace('_', ' ', $tableName))),
                    group: 'wave',
                    wave: (string) $waveNumber,
                    waveLabel: (string) data_get($waveConfig, 'label', 'Wave ' . $waveNumber),
                );
            }
        }

        $sharedTables = [];

        foreach ((array) config('gso.legacy.shared_tables', []) as $tableName) {
            $tableName = (string) $tableName;

            if ($tableName === '' || ! $this->matchesOnlyFilter($tableName, $onlyLookup)) {
                continue;
            }

            $sharedTables[] = $this->inspectTable(
                connection: $connection,
                schema: $schema,
                connectionReachable: $connectionReachable,
                connectionError: $connectionError,
                tableName: $tableName,
                label: Str::headline(str_replace('_', ' ', $tableName)),
                group: 'shared',
                wave: null,
                waveLabel: 'Shared / Platform-Mapped',
            );
        }

        return [
            'connection' => $connection,
            'reference_path' => $referencePath,
            'tables' => $tables,
            'shared_tables' => $sharedTables,
            'summary' => [
                'connection' => $connection,
                'connection_reachable' => $connectionReachable,
                'connection_error' => $connectionError,
                'selected_tables' => count($tables),
                'existing_tables' => $this->countExistingTables($tables),
                'missing_tables' => $this->countMissingTables($tables),
                'selected_shared_tables' => count($sharedTables),
                'existing_shared_tables' => $this->countExistingTables($sharedTables),
                'missing_shared_tables' => $this->countMissingTables($sharedTables),
                'total_rows' => $this->sumRows($tables),
                'shared_total_rows' => $this->sumRows($sharedTables),
            ],
        ];
    }

    private function selectedWaves(string|int|null $wave): array
    {
        $waves = (array) config('gso.legacy.waves', []);

        if ($wave === null || $wave === '' || $wave === 'all') {
            return $waves;
        }

        $waveKey = (string) $wave;

        if (! array_key_exists($waveKey, $waves) && ! array_key_exists((int) $wave, $waves)) {
            throw new InvalidArgumentException("Unknown GSO legacy wave [{$waveKey}].");
        }

        return [
            $waveKey => $waves[$waveKey] ?? $waves[(int) $wave],
        ];
    }

    private function inspectTable(
        string $connection,
        mixed $schema,
        bool $connectionReachable,
        ?string $connectionError,
        string $tableName,
        string $label,
        string $group,
        ?string $wave,
        ?string $waveLabel,
    ): array {
        if (! $connectionReachable || $schema === null) {
            return [
                'group' => $group,
                'wave' => $wave,
                'wave_label' => $waveLabel,
                'table' => $tableName,
                'label' => $label,
                'exists' => false,
                'row_count' => null,
                'columns' => [],
                'id_column' => null,
                'error' => $connectionError,
            ];
        }

        try {
            $exists = $schema->hasTable($tableName);
            $columns = $exists ? $schema->getColumnListing($tableName) : [];
            $rowCount = $exists ? DB::connection($connection)->table($tableName)->count() : null;

            return [
                'group' => $group,
                'wave' => $wave,
                'wave_label' => $waveLabel,
                'table' => $tableName,
                'label' => $label,
                'exists' => $exists,
                'row_count' => $rowCount,
                'columns' => $columns,
                'id_column' => $this->detectIdColumn($columns),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'group' => $group,
                'wave' => $wave,
                'wave_label' => $waveLabel,
                'table' => $tableName,
                'label' => $label,
                'exists' => false,
                'row_count' => null,
                'columns' => [],
                'id_column' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function matchesOnlyFilter(string $tableName, array $onlyLookup): bool
    {
        if ($onlyLookup === []) {
            return true;
        }

        return in_array(Str::lower($tableName), $onlyLookup, true);
    }

    private function detectIdColumn(array $columns): ?string
    {
        foreach (['id', 'uuid'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function countExistingTables(array $tables): int
    {
        return collect($tables)->where('exists', true)->count();
    }

    private function countMissingTables(array $tables): int
    {
        return collect($tables)->where('exists', false)->count();
    }

    private function sumRows(array $tables): int
    {
        return (int) collect($tables)->sum(
            fn (array $table): int => (int) ($table['row_count'] ?? 0)
        );
    }
}
