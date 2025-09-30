<?php
// app/Services/ThemeService.php
namespace App\Services;

use App\Models\AppSetting;
use App\Models\UserPreference;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;

class ThemeService
{
    public function __construct(private Cache $cache) {}

    public static function defaults(): array
    {
        return [
            'style'  => ['mode' => 'light', 'dir' => 'ltr', 'nav' => 'vertical', 'menuHover' => true],
            'colors' => ['primary' => '#635BFF', 'success' => '#22c55e', 'warning' => '#f59e0b', 'danger' => '#ef4444'],
        ];
    }

    /** -------- per-user style -------- */
    public function getUserStyle(string $userId): array
    {
        return $this->cache->remember("theme:user:$userId", 3600, function () use ($userId) {
            $row = UserPreference::where('user_id', $userId)->first();   // ← changed
            return array_replace(self::defaults()['style'], (array) ($row?->theme_style ?? []));
        });
    }

    public function saveUserStyle(string $userId, array $partial): array
    {
        $allowed = Arr::only($partial, ['mode','dir','nav','menuHover']);
        $current = $this->getUserStyle($userId);
        $next    = array_replace($current, $allowed);

        UserPreference::updateOrCreate(['user_id' => $userId], ['theme_style' => $next]);
        $this->cache->forget("theme:user:$userId");

        return $next;
    }

    /** -------- admin-wide colors -------- */
    public function getGlobalColors(): array
    {
        return $this->cache->remember('theme:colors', 3600, function () {
            $row = AppSetting::where('key', 'theme.colors')->first();     // ← changed
            return array_replace(self::defaults()['colors'], (array) ($row?->value ?? []));
        });
    }

    public function saveGlobalColors(array $partial): array
    {
        $allowed = Arr::only($partial, ['primary','success','warning','danger']);
        $next    = array_replace($this->getGlobalColors(), $allowed);

        AppSetting::updateOrCreate(['key' => 'theme.colors'], ['value' => $next]);
        $this->cache->forget('theme:colors');

        return $next;
    }
}
