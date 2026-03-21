<?php

namespace App\Core\Services\UI;

use App\Core\Repositories\Contracts\ThemePreferencesRepositoryInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Arr;
use RuntimeException;

class ThemeService
{
    private const ALLOWED_STYLE = [
        'mode' => ['light', 'dark'],
        'dir' => ['ltr', 'rtl'],
        'nav' => ['vertical', 'horizontal'],
        'menuStyle' => ['menu-click', 'menu-hover', 'icon-click', 'icon-hover'],
        'sideMenuLayout' => ['default', 'closed', 'icontext', 'icon-overlay', 'detached', 'doublemenu'],
        'pageStyle' => ['regular', 'classic', 'modern'],
        'width' => ['fullwidth', 'boxed'],
        'menuPosition' => ['fixed', 'scrollable'],
        'headerPosition' => ['fixed', 'scrollable'],
        'loader' => ['enable', 'disable'],
    ];

    private const ALLOWED_COLOR_KEYS = [
        'menu',
        'header',
        'primaryRgb',
        'primaryRgb1',
        'bodyBgRgb',
        'darkBgRgb',
        'bgImage',
    ];

    public function __construct(
        private Cache $cache,
        private ThemePreferencesRepositoryInterface $repo,
        private CurrentContext $context,
    ) {}

    public static function defaults(): array
    {
        return [
            'style' => [
                'mode' => 'light',
                'dir' => 'ltr',
                'nav' => 'vertical',
                'menuStyle' => 'menu-click',
                'sideMenuLayout' => 'default',
                'pageStyle' => 'regular',
                'width' => 'fullwidth',
                'menuPosition' => 'fixed',
                'headerPosition' => 'fixed',
                'loader' => 'enable',
            ],
            'colors' => [],
        ];
    }

    public function getUserStyle(string $userId): array
    {
        return $this->cache->remember("theme:user:$userId", 3600, function () use ($userId) {
            $stored = (array) $this->repo->getUserStyle($userId);
            $stored = $this->normalizeStyle($stored);

            return array_replace(self::defaults()['style'], $stored);
        });
    }

    public function saveUserStyle(string $userId, array $partial): array
    {
        $allowed = Arr::only($partial, array_keys(self::ALLOWED_STYLE));
        $allowed = $this->normalizeStyle($allowed);

        $next = array_replace($this->getUserStyle($userId), $allowed);

        $this->repo->upsertUserStyle($userId, $next);
        $this->cache->forget("theme:user:$userId");

        return $next;
    }

    public function getModuleColors(?string $moduleId = null): array
    {
        $resolvedModuleId = $moduleId ?: $this->context->moduleId();

        if ($resolvedModuleId === null || $resolvedModuleId === '') {
            return self::defaults()['colors'];
        }

        return $this->cache->remember("theme:colors:module:$resolvedModuleId", 3600, function () use ($resolvedModuleId) {
            return $this->normalizeColors($this->repo->getModuleColors($resolvedModuleId));
        });
    }

    public function saveModuleColors(array $payload, ?string $moduleId = null): array
    {
        $resolvedModuleId = $moduleId ?: $this->context->moduleId();

        if ($resolvedModuleId === null || $resolvedModuleId === '') {
            throw new RuntimeException('Active module context is required to save theme colors.');
        }

        $next = $this->normalizeColors($payload);

        $this->repo->upsertModuleColors($resolvedModuleId, $next);
        $this->cache->forget("theme:colors:module:$resolvedModuleId");

        return $next;
    }

    private function normalizeStyle(array $in): array
    {
        $out = $in;

        if (isset($out['menu_style']) && ! isset($out['menuStyle'])) {
            $out['menuStyle'] = $out['menu_style'];
        }

        if (array_key_exists('menuHover', $out) && ! isset($out['menuStyle'])) {
            $hover = filter_var($out['menuHover'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($hover !== null) {
                $out['menuStyle'] = $hover ? 'menu-hover' : 'menu-click';
            }
        }

        if (isset($out['page_style']) && ! isset($out['pageStyle'])) {
            $out['pageStyle'] = $out['page_style'];
        }

        if (isset($out['data-page-styles']) && ! isset($out['pageStyle'])) {
            $out['pageStyle'] = $out['data-page-styles'];
        }

        if (isset($out['layout-width']) && ! isset($out['width'])) {
            $out['width'] = $out['layout-width'];
        }

        if (isset($out['data-menu-positions']) && ! isset($out['menuPosition'])) {
            $out['menuPosition'] = $out['data-menu-positions'];
        }

        if (isset($out['data-header-positions']) && ! isset($out['headerPosition'])) {
            $out['headerPosition'] = $out['data-header-positions'];
        }

        if (isset($out['page-loader']) && ! isset($out['loader'])) {
            $out['loader'] = $out['page-loader'];
        }

        unset(
            $out['menu_style'],
            $out['menuHover'],
            $out['page_style'],
            $out['data-page-styles'],
            $out['layout-width'],
            $out['data-menu-positions'],
            $out['data-header-positions'],
            $out['page-loader']
        );

        foreach ($out as $key => $value) {
            if (is_string($value)) {
                $out[$key] = strtolower(trim($value));
            }
        }

        $clean = [];
        foreach (self::ALLOWED_STYLE as $key => $allowedValues) {
            if (isset($out[$key]) && in_array($out[$key], $allowedValues, true)) {
                $clean[$key] = $out[$key];
            }
        }

        return $clean;
    }

    private function normalizeColors(array $payload): array
    {
        $allowed = Arr::only($payload, self::ALLOWED_COLOR_KEYS);
        $clean = [];

        foreach ($allowed as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean;
    }
}
