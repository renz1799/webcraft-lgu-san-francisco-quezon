<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;
use App\Repositories\Contracts\ThemePreferencesRepositoryInterface;

class ThemeService
{
    public function __construct(
        private Cache $cache,
        private ThemePreferencesRepositoryInterface $repo
    ) {}

    public static function defaults(): array
    {
        // NOTE: we keep menuStyle as canonical; we’ll still accept old menuHover on input
        return [
            'style'  => [
                'mode'      => 'light',
                'dir'       => 'ltr',
                'nav'       => 'vertical',
                'menuStyle' => 'menu-click',   // menu-click | menu-hover | icon-click | icon-hover
            ],
            'colors' => [
                'primary' => '#635BFF',
                'success' => '#22c55e',
                'warning' => '#f59e0b',
                'danger'  => '#ef4444',
            ],
        ];
    }

    /** -------- per-user style -------- */
    public function getUserStyle(string $userId): array
    {
        return $this->cache->remember("theme:user:$userId", 3600, function () use ($userId) {
            $stored = $this->repo->getUserStyle($userId);
            return array_replace(self::defaults()['style'], (array) $stored);
        });
    }

    public function saveUserStyle(string $userId, array $partial): array
    {
        // accept only canonical keys
        $allowed = Arr::only($partial, ['mode','dir','nav','menuStyle']);
        $next = array_replace($this->getUserStyle($userId), $allowed);

        $this->repo->upsertUserStyle($userId, $next);
        $this->cache->forget("theme:user:$userId");

        return $next;
    }

    /** -------- admin-wide colors -------- */
    public function getGlobalColors(): array
    {
        return $this->cache->remember('theme:colors', 3600, function () {
            $stored = $this->repo->getGlobalColors();
            return array_replace(self::defaults()['colors'], (array) $stored);
        });
    }

    public function saveGlobalColors(array $partial): array
    {
        $allowed = Arr::only($partial, ['primary','success','warning','danger']);
        $next = array_replace($this->getGlobalColors(), $allowed);

        $this->repo->upsertGlobalColors($next);
        $this->cache->forget('theme:colors');

        return $next;
    }
}
