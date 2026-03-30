<?php

namespace App\Core\Services\Infrastructure;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use RuntimeException;

class HybridPdfGenerator implements PdfGeneratorInterface
{
    public function __construct(
        private readonly ChromePdfGenerator $chrome,
        private readonly DompdfPdfGenerator $dompdf,
    ) {}

    public function generateFromView(string $view, array $data, string $outputPath): string
    {
        return $this->runWithConfiguredDriver(
            fn (PdfGeneratorInterface $generator): string => $generator->generateFromView($view, $data, $outputPath),
        );
    }

    public function generateFromHtml(string $html, string $outputPath): string
    {
        return $this->runWithConfiguredDriver(
            fn (PdfGeneratorInterface $generator): string => $generator->generateFromHtml($html, $outputPath),
        );
    }

    public function generateFromHtmlFile(string $htmlPath, string $outputPath): string
    {
        return $this->runWithConfiguredDriver(
            fn (PdfGeneratorInterface $generator): string => $generator->generateFromHtmlFile($htmlPath, $outputPath),
        );
    }

    public function resolveDriver(): string
    {
        $driver = $this->configuredDriver();

        if ($driver === 'dompdf') {
            return 'dompdf';
        }

        if ($driver === 'chrome') {
            return 'chrome';
        }

        return $this->chrome->isAvailable() ? 'chrome' : 'dompdf';
    }

    private function configuredDriver(): string
    {
        return match (config('pdf.driver', 'auto')) {
            'chrome', 'dompdf' => config('pdf.driver', 'auto'),
            default => 'auto',
        };
    }

    /**
     * @param  callable(PdfGeneratorInterface): string  $callback
     */
    private function runWithConfiguredDriver(callable $callback): string
    {
        $driver = $this->resolveDriver();

        if ($driver === 'dompdf') {
            return $callback($this->dompdf);
        }

        if ($driver === 'chrome') {
            return $callback($this->chrome);
        }

        if (! $this->chrome->isAvailable()) {
            return $callback($this->dompdf);
        }

        try {
            return $callback($this->chrome);
        } catch (RuntimeException) {
            return $callback($this->dompdf);
        }
    }
}
