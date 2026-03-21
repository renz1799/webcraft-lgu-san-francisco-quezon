<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class ArchitectureGuardrailsTest extends TestCase
{
    public function test_core_must_not_reference_module_namespaces(): void
    {
        $violations = [];

        foreach ($this->phpFilesIn(base_path('app/Core')) as $file) {
            $contents = file_get_contents($file);

            if ($contents !== false && str_contains($contents, 'App\\Modules\\')) {
                $violations[] = $this->relativePath($file);
            }
        }

        $this->assertSame(
            [],
            $violations,
            "Core must not reference module namespaces.\n" . implode("\n", $violations)
        );
    }

    public function test_legacy_top_level_app_concern_directories_do_not_exist(): void
    {
        foreach ([
            'app/Builders',
            'app/Data',
            'app/Policies',
            'app/Providers',
            'app/Repositories',
            'app/Services',
            'app/Support',
        ] as $path) {
            $this->assertDirectoryDoesNotExist(base_path($path), "Legacy top-level path still exists: {$path}");
        }
    }

    public function test_only_intentional_framework_base_files_remain_at_top_level_app_http_and_models(): void
    {
        $httpFiles = $this->relativePhpFilesIn(base_path('app/Http'));
        $modelFiles = $this->relativePhpFilesIn(base_path('app/Models'));

        sort($httpFiles);
        sort($modelFiles);

        $this->assertSame([
            'app/Http/Controllers/Controller.php',
            'app/Http/Requests/BaseFormRequest.php',
        ], $httpFiles);

        $this->assertSame([
            'app/Models/Concerns/HasUuid.php',
        ], $modelFiles);
    }

    public function test_legacy_top_level_resource_roots_do_not_exist(): void
    {
        $this->assertDirectoryDoesNotExist(base_path('resources/js'));
        $this->assertDirectoryDoesNotExist(base_path('resources/views'));
        $this->assertDirectoryDoesNotExist(base_path('resources/core/js/tasks'));
        $this->assertDirectoryDoesNotExist(base_path('resources/core/views/tasks'));
    }

    /**
     * @return list<string>
     */
    private function relativePhpFilesIn(string $directory): array
    {
        return array_map(
            fn (string $file): string => $this->relativePath($file),
            $this->phpFilesIn($directory)
        );
    }

    /**
     * @return list<string>
     */
    private function phpFilesIn(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function relativePath(string $path): string
    {
        return str_replace('\\', '/', substr($path, strlen(base_path()) + 1));
    }
}
