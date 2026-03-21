<?php

namespace App\Core\Services\Contracts\Infrastructure;

interface PdfGeneratorInterface
{
    public function generateFromView(string $view, array $data, string $outputPath): string;
}
