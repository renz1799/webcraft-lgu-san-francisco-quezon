<?php

namespace App\Core\Repositories\Contracts;

interface ThemePreferencesRepositoryInterface
{
    // per-user style
    public function getUserStyle(string $userId): array;
    public function upsertUserStyle(string $userId, array $style): void;

    // module-wide theme colors
    public function getModuleColors(string $moduleId): array;
    public function upsertModuleColors(string $moduleId, array $colors): void;
}
