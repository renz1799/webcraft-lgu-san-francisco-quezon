<?php

namespace App\Services\Infrastructure;

use App\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class ChromePdfGenerator implements PdfGeneratorInterface
{
    public function generateFromView(string $view, array $data, string $outputPath): string
    {
        $tempRoot = storage_path('app/tmp/pdf-generator');
        $token = (string) Str::uuid();
        $workingDir = $tempRoot . DIRECTORY_SEPARATOR . $token;
        $profileDir = $workingDir . DIRECTORY_SEPARATOR . 'chrome-profile';
        $htmlPath = $workingDir . DIRECTORY_SEPARATOR . 'document.html';

        File::ensureDirectoryExists($profileDir);
        File::ensureDirectoryExists(dirname($outputPath));

        try {
            File::put($htmlPath, View::make($view, $data)->render());

            $process = new Process([
                $this->resolveChromeBinary(),
                '--user-data-dir=' . $profileDir,
                '--headless=new',
                '--disable-gpu',
                '--no-pdf-header-footer',
                '--print-to-pdf=' . $outputPath,
                $this->toFileUrl($htmlPath),
            ]);

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
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            'Chrome/Chromium binary not found. Set CHROME_BIN or services.chrome.binary.'
        );
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
}