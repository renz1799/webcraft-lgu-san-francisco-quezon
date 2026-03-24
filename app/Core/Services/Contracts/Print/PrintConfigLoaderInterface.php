<?php

namespace App\Core\Services\Contracts\Print;

interface PrintConfigLoaderInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function papers(): array;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function printables(): array;

    /**
     * @return array<string, mixed>
     */
    public function printable(string $code): array;

    public function defaultPaper(string $printableCode, string $fallback = 'a4-portrait'): string;

    /**
     * @return array<int, string>
     */
    public function allowedPapers(string $printableCode, ?string $fallback = null): array;

    /**
     * @return array<string, mixed>
     */
    public function resolvePaperProfile(string $printableCode, ?string $requestedPaper): array;
}
