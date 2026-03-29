<?php

namespace App\Core\Services\Contracts\Infrastructure;

interface PdfGeneratorInterface
{
    public function generateFromView(string $view, array $data, string $outputPath): string;

    public function generateFromHtml(string $html, string $outputPath): string;

    public function generateFromHtmlFile(string $htmlPath, string $outputPath): string;
}
