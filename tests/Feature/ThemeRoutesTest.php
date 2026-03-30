<?php

namespace Tests\Feature;

use Tests\TestCase;

class ThemeRoutesTest extends TestCase
{
    public function test_theme_routes_are_registered_with_expected_actions_and_middleware(): void
    {
        $styleRoute = app('router')->getRoutes()->getByName('theme.style.update');
        $colorsRoute = app('router')->getRoutes()->getByName('theme.colors.update');

        $this->assertNotNull($styleRoute);
        $this->assertNotNull($colorsRoute);

        $this->assertSame(['POST'], $styleRoute->methods());
        $this->assertSame('App\\Core\\Http\\Controllers\\Settings\\ThemeController@updateStyle', $styleRoute->getActionName());
        $this->assertSame(['POST'], $colorsRoute->methods());
        $this->assertSame('App\\Core\\Http\\Controllers\\Settings\\ThemeController@updateColors', $colorsRoute->getActionName());

        $this->assertContains('auth', $styleRoute->gatherMiddleware());
        $this->assertContains('password.changed', $styleRoute->gatherMiddleware());
        $this->assertContains('permission:theme.update_colors', $colorsRoute->gatherMiddleware());
    }
}
