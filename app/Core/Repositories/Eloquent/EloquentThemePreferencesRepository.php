<?php

namespace App\Core\Repositories\Eloquent;

use App\Core\Models\AppSetting;
use App\Core\Models\UserPreference;
use App\Core\Repositories\Contracts\ThemePreferencesRepositoryInterface;

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

    public function getModuleColors(string $moduleId): array
    {
        $row = AppSetting::query()
            ->where('module_id', $moduleId)
            ->where('key', 'theme.colors')
            ->first();

        return (array) ($row?->value ?? []);
    }

    public function upsertModuleColors(string $moduleId, array $colors): void
    {
        AppSetting::updateOrCreate(
            [
                'module_id' => $moduleId,
                'key' => 'theme.colors',
            ],
            ['value' => $colors]
        );
    }
}
