<?php

namespace App\Core\Services\Print;

use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;

class PrintConfigLoaderService implements PrintConfigLoaderInterface
{
    public function papers(): array
    {
        return $this->normalizeAssoc(config('print.papers', []));
    }

    public function printables(): array
    {
        $legacy = $this->normalizeAssoc(config('print.modules', []));
        $canonical = $this->normalizeAssoc(config('printables', []));

        return $this->mergeConfigTrees($legacy, $canonical);
    }

    public function printable(string $code): array
    {
        $code = trim($code);

        if ($code === '') {
            return [];
        }

        return $this->normalizeAssoc($this->printables()[$code] ?? []);
    }

    public function defaultPaper(string $printableCode, string $fallback = 'a4-portrait'): string
    {
        $configured = (string) ($this->printable($printableCode)['default_paper'] ?? '');

        return $configured !== '' ? $configured : $fallback;
    }

    public function allowedPapers(string $printableCode, ?string $fallback = null): array
    {
        $defaultPaper = $this->defaultPaper($printableCode, $fallback ?? 'a4-portrait');
        $allowed = $this->printable($printableCode)['allowed_papers'] ?? [$defaultPaper];

        if (!is_array($allowed) || $allowed === []) {
            return [$defaultPaper];
        }

        $normalized = array_values(array_filter(array_map(
            static fn (mixed $value): ?string => is_string($value) && trim($value) !== '' ? trim($value) : null,
            $allowed,
        )));

        return $normalized !== [] ? array_values(array_unique($normalized)) : [$defaultPaper];
    }

    public function resolvePaperProfile(string $printableCode, ?string $requestedPaper): array
    {
        $defaultPaper = $this->defaultPaper($printableCode);
        $allowedPapers = $this->allowedPapers($printableCode, $defaultPaper);

        $paperCode = in_array($requestedPaper, $allowedPapers, true)
            ? $requestedPaper
            : $defaultPaper;

        $paperDefaults = $this->normalizeAssoc($this->papers()[$paperCode] ?? []);
        $printable = $this->printable($printableCode);
        $printableProfile = $this->normalizeAssoc($printable['profiles'][$paperCode] ?? []);
        $resolved = array_merge($paperDefaults, $printableProfile);

        if ($resolved === []) {
            $fallbackDefaults = $this->normalizeAssoc($this->papers()[$defaultPaper] ?? []);
            $fallbackProfile = $this->normalizeAssoc($printable['profiles'][$defaultPaper] ?? []);
            $resolved = array_merge($fallbackDefaults, $fallbackProfile);
        }

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeAssoc(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function mergeConfigTrees(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            $existing = $base[$key] ?? null;

            if (
                is_array($existing)
                && is_array($value)
                && !array_is_list($existing)
                && !array_is_list($value)
            ) {
                $base[$key] = $this->mergeConfigTrees($existing, $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }
}
