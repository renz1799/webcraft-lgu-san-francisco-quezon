<?php

use App\Modules\GSO\Support\LegacyGsoInspector;
use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('gso:legacy:inspect {--wave=all} {--only=*} {--json}', function (LegacyGsoInspector $inspector) {
    $report = $inspector->inspect(
        $this->option('wave'),
        (array) $this->option('only')
    );

    if ($this->option('json')) {
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $report['summary']['connection_reachable'] ? 0 : 1;
    }

    $summary = $report['summary'];

    $this->info('Legacy connection: ' . $summary['connection']);
    $this->line('Reference path: ' . $report['reference_path']);

    if (! $summary['connection_reachable']) {
        $this->error('Could not reach the configured legacy GSO connection.');
        $this->line((string) $summary['connection_error']);

        return 1;
    }

    if ($report['tables'] !== []) {
        $this->table(
            ['Wave', 'Table', 'Exists', 'Rows', 'ID Column', 'Error'],
            collect($report['tables'])->map(function (array $table): array {
                return [
                    $table['wave_label'],
                    $table['table'],
                    $table['exists'] ? 'yes' : 'no',
                    $table['row_count'] ?? '-',
                    $table['id_column'] ?? '-',
                    $table['error'] ?? '-',
                ];
            })->all()
        );
    }

    if ($report['shared_tables'] !== []) {
        $this->table(
            ['Shared Table', 'Exists', 'Rows', 'ID Column', 'Error'],
            collect($report['shared_tables'])->map(function (array $table): array {
                return [
                    $table['table'],
                    $table['exists'] ? 'yes' : 'no',
                    $table['row_count'] ?? '-',
                    $table['id_column'] ?? '-',
                    $table['error'] ?? '-',
                ];
            })->all()
        );
    }

    $this->info(
        sprintf(
            'Wave tables present: %d/%d, shared tables present: %d/%d.',
            $summary['existing_tables'],
            $summary['selected_tables'],
            $summary['existing_shared_tables'],
            $summary['selected_shared_tables'],
        )
    );
    $this->line(
        sprintf(
            'Wave rows: %d, shared rows: %d.',
            $summary['total_rows'],
            $summary['shared_total_rows'],
        )
    );

    return ($summary['missing_tables'] + $summary['missing_shared_tables']) > 0 ? 1 : 0;
})->purpose('Inspect legacy GSO tables and shared platform-mapped tables on the configured legacy connection.');

Artisan::command('gso:legacy:import-reference {--only=*} {--dry-run} {--json}', function (LegacyReferenceDataImporter $importer) {
    $report = $importer->import(
        (array) $this->option('only'),
        (bool) $this->option('dry-run'),
    );

    if ($this->option('json')) {
        $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return 0;
    }

    $summary = $report['summary'];

    $this->info('Legacy connection: ' . $summary['connection']);
    $this->line('Mode: ' . ($summary['dry_run'] ? 'dry-run' : 'write'));

    $this->table(
        ['Table', 'Rows Read', 'Rows Written', 'Status'],
        collect($report['tables'])->map(function (array $table): array {
            return [
                $table['table'],
                $table['rows_read'],
                $table['rows_written'],
                $table['status'],
            ];
        })->all()
    );

    $this->info(
        sprintf(
            'Imported %d table(s), read %d row(s), wrote %d row(s).',
            $summary['selected_tables'],
            $summary['total_rows_read'],
            $summary['total_rows_written'],
        )
    );

    return 0;
})->purpose('Import legacy GSO reference data into the platform tables while preserving source UUIDs.');
