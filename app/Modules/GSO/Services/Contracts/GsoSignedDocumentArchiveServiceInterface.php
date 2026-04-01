<?php

namespace App\Modules\GSO\Services\Contracts;

interface GsoSignedDocumentArchiveServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function archive(string $documentType, string $documentNumber, string $pdfPath): array;
}
