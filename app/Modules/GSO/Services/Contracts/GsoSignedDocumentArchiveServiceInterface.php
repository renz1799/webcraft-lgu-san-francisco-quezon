<?php

namespace App\Modules\GSO\Services\Contracts;

interface GsoSignedDocumentArchiveServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function archive(string $documentType, string $documentNumber, string $pdfPath): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findArchived(string $documentType, string $documentNumber): ?array;

    /**
     * @return array{name: string, mime_type: string, bytes: string}
     */
    public function downloadArchived(string $documentType, string $documentNumber): array;
}
