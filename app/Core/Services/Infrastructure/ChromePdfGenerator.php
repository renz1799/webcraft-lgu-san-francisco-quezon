<?php

namespace App\Core\Services\Infrastructure;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ChromePdfGenerator implements PdfGeneratorInterface
{
    public function isAvailable(): bool
    {
        return $this->detectChromeBinary() !== null;
    }

    public function generateFromView(string $view, array $data, string $outputPath): string
    {
        return $this->generateFromHtml(
            View::make($view, $data)->render(),
            $outputPath,
        );
    }

    public function generateFromHtml(string $html, string $outputPath): string
    {
        $tempRoot = storage_path('app/tmp/pdf-generator');
        $token = (string) Str::uuid();
        $workingDir = $tempRoot . DIRECTORY_SEPARATOR . $token;
        $htmlPath = $workingDir . DIRECTORY_SEPARATOR . 'document.html';

        try {
            File::ensureDirectoryExists($workingDir);
            File::put($htmlPath, $html);

            return $this->generateFromHtmlFile($htmlPath, $outputPath);
        } finally {
            File::deleteDirectory($workingDir);
        }
    }

    public function generateFromHtmlFile(string $htmlPath, string $outputPath): string
    {
        $tempRoot = storage_path('app/tmp/pdf-generator');
        $token = (string) Str::uuid();
        $workingDir = $tempRoot . DIRECTORY_SEPARATOR . $token;
        $profileDir = $workingDir . DIRECTORY_SEPARATOR . 'chrome-profile';
        $tempDir = $workingDir . DIRECTORY_SEPARATOR . 'chrome-temp';

        File::ensureDirectoryExists($profileDir);
        File::ensureDirectoryExists($tempDir);
        File::ensureDirectoryExists(dirname($outputPath));

        try {
            $process = new Process([
                $this->resolveChromeBinary(),
                '--user-data-dir=' . $profileDir,
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-crash-reporter',
                '--disable-crashpad-for-testing',
                '--no-first-run',
                '--no-default-browser-check',
                '--disable-background-networking',
                '--disable-component-update',
                '--disable-sync',
                '--metrics-recording-only',
                '--allow-file-access-from-files',
                '--no-pdf-header-footer',
                '--print-to-pdf=' . $outputPath,
                $this->toFileUrl($htmlPath),
            ], null, $this->chromeEnvironmentOverrides($workingDir, $tempDir));

            $process->setTimeout(120);
            $process->run();

            if (! is_file($outputPath)) {
                throw new RuntimeException(
                    'Failed to generate PDF using Chrome. ' . $process->getErrorOutput()
                );
            }

            return $outputPath;
        } finally {
            File::deleteDirectory($workingDir);
        }
    }

    private function resolveChromeBinary(): string
    {
        $binary = $this->detectChromeBinary();

        if ($binary !== null) {
            return $binary;
        }

        throw new RuntimeException(
            'Chrome/Chromium binary not found. Set CHROME_BIN or services.chrome.binary.'
        );
    }

    private function detectChromeBinary(): ?string
    {
        $configured = config('services.chrome.binary');

        $candidates = array_filter([
            is_string($configured) && $configured !== '' ? $configured : null,
            env('CHROME_BIN'),

            // Windows - Google Chrome
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',

            // Windows - Microsoft Edge
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',

            // Linux
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/local/bin/google-chrome',
            '/usr/local/bin/google-chrome-stable',
            '/usr/local/bin/chromium',
            '/usr/local/bin/chromium-browser',
            '/opt/google/chrome/chrome',
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function toFileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        if (preg_match('/^([A-Za-z]):\/(.*)$/', $normalized, $matches) === 1) {
            $segments = array_map(
                static fn (string $segment): string => rawurlencode($segment),
                array_values(array_filter(
                    explode('/', $matches[2]),
                    static fn (string $segment): bool => $segment !== ''
                ))
            );

            return 'file:///' . $matches[1] . ':/' . implode('/', $segments);
        }

        return 'file:///' . ltrim($normalized, '/');
    }

    /**
     * @return array<string, string>
     */
    private function chromeEnvironmentOverrides(string $workingDir, string $tempDir): array
    {
        return [
            'APPDATA' => $workingDir,
            'HOME' => $workingDir,
            'LOCALAPPDATA' => $workingDir,
            'TEMP' => $tempDir,
            'TMP' => $tempDir,
            'USERPROFILE' => $workingDir,
        ];
    }
}
