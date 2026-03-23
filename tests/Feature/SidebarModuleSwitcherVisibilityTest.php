<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use Tests\TestCase;

class SidebarModuleSwitcherVisibilityTest extends TestCase
{
    public function test_module_switcher_is_hidden_when_user_has_only_one_accessible_context(): void
    {
        $html = view('layouts.components.sidebar.sections.module-switcher', [
            'moduleLinks' => collect([
                (object) [
                    'code' => 'GSO',
                    'name' => 'General Services Office',
                ],
            ]),
            'currentModule' => (object) [
                'code' => 'GSO',
                'name' => 'General Services Office',
            ],
        ])->render();

        $this->assertStringNotContainsString('Platform Contexts', $html);
        $this->assertStringNotContainsString('Context Selector', $html);
    }

    public function test_module_switcher_is_shown_when_user_has_multiple_accessible_contexts(): void
    {
        $moduleLinks = collect([
            (object) [
                'code' => 'CORE',
                'name' => 'Core Platform',
            ],
            (object) [
                'code' => 'GSO',
                'name' => 'General Services Office',
            ],
        ]);

        $html = view('layouts.components.sidebar.sections.module-switcher', [
            'moduleLinks' => $moduleLinks,
            'currentModule' => $moduleLinks->first(),
        ])->render();

        $this->assertStringContainsString('Platform Contexts', $html);
        $this->assertStringContainsString('Context Selector', $html);
        $this->assertStringContainsString('Core Platform', $html);
        $this->assertStringContainsString('General Services Office', $html);
    }
}
