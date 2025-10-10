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
        'pageStyle'      => ['regular','classic','modern'],
        'width'          => ['fullwidth','boxed'],
        'menuPosition'   => ['fixed','scrollable'],
        'headerPosition' => ['fixed','scrollable'],
        'loader'         => ['enable','disable'],
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
                'pageStyle'     => 'regular', 
                'width'          => 'fullwidth',
                'menuPosition'   => 'fixed',
                'headerPosition' => 'fixed',
                'loader'         => 'enable',
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

        // legacy menu aliases (as before) ...
        if (isset($out['menu_style']) && !isset($out['menuStyle'])) $out['menuStyle'] = $out['menu_style'];
        if (array_key_exists('menuHover', $out) && !isset($out['menuStyle'])) {
            $hover = filter_var($out['menuHover'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hover !== null) $out['menuStyle'] = $hover ? 'menu-hover' : 'menu-click';
        }

        // pageStyle aliases
        if (isset($out['page_style']) && !isset($out['pageStyle'])) $out['pageStyle'] = $out['page_style'];
        if (isset($out['data-page-styles']) && !isset($out['pageStyle'])) $out['pageStyle'] = $out['data-page-styles'];

        // width / positions / loader aliases
        if (isset($out['layout-width']) && !isset($out['width'])) $out['width'] = $out['layout-width'];
        if (isset($out['data-menu-positions']) && !isset($out['menuPosition'])) $out['menuPosition'] = $out['data-menu-positions'];
        if (isset($out['data-header-positions']) && !isset($out['headerPosition'])) $out['headerPosition'] = $out['data-header-positions'];
        if (isset($out['page-loader']) && !isset($out['loader'])) $out['loader'] = $out['page-loader'];

        // drop aliases
        unset(
            $out['menu_style'], $out['menuHover'],
            $out['page_style'], $out['data-page-styles'],
            $out['layout-width'], $out['data-menu-positions'], $out['data-header-positions'], $out['page-loader']
        );

        // lowercase/trim strings
        foreach ($out as $k => $v) if (is_string($v)) $out[$k] = strtolower(trim($v));

        // clamp to allowed sets
        $clean = [];
        foreach (self::ALLOWED_STYLE as $key => $allowedVals) {
            if (isset($out[$key]) && in_array($out[$key], $allowedVals, true)) {
                $clean[$key] = $out[$key];
            }
        }
        return $clean;
    }
}
