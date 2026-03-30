<?php

namespace App\Core\Services\Infrastructure;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class DompdfPdfGenerator implements PdfGeneratorInterface
{
    public function generateFromView(string $view, array $data, string $outputPath): string
    {
        return $this->generateFromHtml(
            View::make($view, $data + ['pdfEngine' => 'dompdf'])->render(),
            $outputPath,
        );
    }

    public function generateFromHtml(string $html, string $outputPath): string
    {
        File::ensureDirectoryExists(dirname($outputPath));

        $dompdf = new Dompdf($this->options());
        $dompdf->setBasePath(base_path());
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        File::put($outputPath, $dompdf->output());

        return $outputPath;
    }

    public function generateFromHtmlFile(string $htmlPath, string $outputPath): string
    {
        return $this->generateFromHtml(
            File::get($htmlPath),
            $outputPath,
        );
    }

    private function options(): Options
    {
        $tempRoot = storage_path('app/tmp/dompdf');
        $fontRoot = $tempRoot . DIRECTORY_SEPARATOR . 'fonts';

        File::ensureDirectoryExists($tempRoot);
        File::ensureDirectoryExists($fontRoot);

        return new Options([
            'chroot' => [
                base_path(),
                public_path(),
                storage_path(),
            ],
            'defaultMediaType' => 'print',
            'defaultFont' => 'DejaVu Sans',
            'dpi' => (int) config('pdf.dompdf.dpi', 96),
            'fontCache' => $fontRoot,
            'fontDir' => $fontRoot,
            'isFontSubsettingEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'isJavascriptEnabled' => false,
            'isRemoteEnabled' => true,
            'tempDir' => $tempRoot,
        ]);
    }
}
