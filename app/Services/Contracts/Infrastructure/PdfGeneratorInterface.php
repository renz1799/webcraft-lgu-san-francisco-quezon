<?php

namespace App\Services\Contracts\Infrastructure;

interface PdfGeneratorInterface
{
    public function generateFromView(string $view, array $data, string $outputPath): string;
}