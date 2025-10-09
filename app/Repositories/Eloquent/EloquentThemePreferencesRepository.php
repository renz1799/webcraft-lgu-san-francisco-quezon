<?php

namespace App\Repositories\Eloquent;

use App\Models\UserPreference;
use App\Models\AppSetting;
use App\Repositories\Contracts\ThemePreferencesRepositoryInterface;

class EloquentThemePreferencesRepository implements ThemePreferencesRepositoryInterface
{
    public function getUserStyle(string $userId): array
    {
        $row = UserPreference::query()
            ->where('user_id', $userId)
            ->first();

        return (array) ($row?->theme_style ?? []);
    }

    public function upsertUserStyle(string $userId, array $style): void
    {
        UserPreference::updateOrCreate(
            ['user_id' => $userId],
            ['theme_style' => $style]
        );
    }

    public function getGlobalColors(): array
    {
        $row = AppSetting::query()
            ->where('key', 'theme.colors')
            ->first();

        return (array) ($row?->value ?? []);
    }

    public function upsertGlobalColors(array $colors): void
    {
        AppSetting::updateOrCreate(
            ['key' => 'theme.colors'],
            ['value' => $colors]
        );
    }
}
