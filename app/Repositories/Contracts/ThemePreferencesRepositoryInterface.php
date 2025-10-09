<?php

namespace App\Repositories\Contracts;

interface ThemePreferencesRepositoryInterface
{
    // per-user style
    public function getUserStyle(string $userId): array;
    public function upsertUserStyle(string $userId, array $style): void;

    // global colors (admin)
    public function getGlobalColors(): array;
    public function upsertGlobalColors(array $colors): void;
}
