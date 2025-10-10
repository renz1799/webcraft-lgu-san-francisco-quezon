<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;
use App\Repositories\Contracts\ThemePreferencesRepositoryInterface;

class ThemeService
{
    private const ALLOWED_STYLE = [
        'mode'           => ['light','dark'],
        'dir'            => ['ltr','rtl'],
        'nav'            => ['vertical','horizontal'],
        'menuStyle'      => ['menu-click','menu-hover','icon-click','icon-hover'],
        'sideMenuLayout' => ['default','closed','icontext','icon-overlay','detached','doublemenu'],
    ];

    public function __construct(
        private Cache $cache,
        private ThemePreferencesRepositoryInterface $repo
    ) {}

    public static function defaults(): array
    {
        return [
            'style'  => [
                'mode'           => 'light',
                'dir'            => 'ltr',
                'nav'            => 'vertical',
                'menuStyle'      => 'menu-click',
                'sideMenuLayout' => 'default',
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
            $stored = (array) $this->repo->getUserStyle($userId);
            $stored = $this->normalizeStyle($stored); // <- migrate/clean old records
            return array_replace(self::defaults()['style'], $stored);
        });
    }

    public function saveUserStyle(string $userId, array $partial): array
    {
        // keep only canonical keys, normalize values
        $allowed = Arr::only($partial, array_keys(self::ALLOWED_STYLE));
        $allowed = $this->normalizeStyle($allowed);

        $next = array_replace($this->getUserStyle($userId), $allowed);

        $this->repo->upsertUserStyle($userId, $next);
        $this->cache->forget("theme:user:$userId");

        return $next;
    }

    /** -------- admin-wide colors -------- */
    public function getGlobalColors(): array
    {
        return $this->cache->remember('theme:colors', 3600, function () {
            $stored = (array) $this->repo->getGlobalColors();
            return array_replace(self::defaults()['colors'], $stored);
        });
    }

    public function saveGlobalColors(array $partial): array
    {
        $allowed = Arr::only($partial, ['primary','success','warning','danger']);
        $next    = array_replace($this->getGlobalColors(), $allowed);

        $this->repo->upsertGlobalColors($next);
        $this->cache->forget('theme:colors');

        return $next;
    }

    /** -------- helpers -------- */

    /**
     * Normalize/migrate to canonical keys and clamp values to allowed sets.
     * Handles legacy keys from older rows:
     *  - menu_style -> menuStyle
     *  - menuHover  -> menuStyle (click/hover best-guess)
     */
    private function normalizeStyle(array $in): array
    {
        $out = $in;

        // legacy aliases -> canonical
        if (isset($out['menu_style']) && !isset($out['menuStyle'])) {
            $out['menuStyle'] = $out['menu_style'];
        }
        if (array_key_exists('menuHover', $out) && !isset($out['menuStyle'])) {
            $hover = filter_var($out['menuHover'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hover !== null) {
                $out['menuStyle'] = $hover ? 'menu-hover' : 'menu-click';
            }
        }

        // drop aliases
        unset($out['menu_style'], $out['menuHover']);

        // lowercase/trim strings
        foreach ($out as $k => $v) {
            if (is_string($v)) $out[$k] = strtolower(trim($v));
        }

        // clamp values to allowed sets, fallback to defaults
        $clean = [];
        foreach (self::ALLOWED_STYLE as $key => $allowedVals) {
            if (isset($out[$key]) && in_array($out[$key], $allowedVals, true)) {
                $clean[$key] = $out[$key];
            }
        }

        return $clean;
    }
}
