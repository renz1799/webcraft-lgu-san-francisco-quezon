<?php

namespace Tests\Feature;

use App\Builders\AuditLogs\AuditLogMetaBuilder;
use Tests\TestCase;

class AuditLogMetaBuilderTest extends TestCase
{
    public function test_build_merges_and_normalizes_display_payload_into_meta(): void
    {
        $result = (new AuditLogMetaBuilder())->build(
            meta: ['source' => 'users.edit'],
            display: [
                'summary' => '  Permissions updated for Craig Scot Schamberger  ',
                'subject_label' => ' Craig Scot Schamberger ',
                'sections' => [
                    [
                        'title' => ' Direct Permissions ',
                        'items' => [
                            ['label' => 'Added', 'value' => ['Modify Tasks']],
                            ['label' => 'Removed'],
                        ],
                    ],
                    ['title' => ' ', 'items' => []],
                ],
                'request_details' => [
                    'Reference No' => 'PO-2026-0312',
                    'Empty' => '',
                ],
                'system_notes' => [
                    [
                        'title' => 'Resolved selections',
                        'items' => ['Manage Tasks / Tasks / Edit', ''],
                    ],
                ],
            ],
        );

        $this->assertSame('users.edit', $result['source']);
        $this->assertSame('Permissions updated for Craig Scot Schamberger', $result['display']['summary']);
        $this->assertSame('Craig Scot Schamberger', $result['display']['subject_label']);
        $this->assertCount(1, $result['display']['sections']);
        $this->assertSame('Direct Permissions', $result['display']['sections'][0]['title']);
        $this->assertCount(1, $result['display']['sections'][0]['items']);
        $this->assertSame('PO-2026-0312', $result['display']['request_details']['Reference No']);
        $this->assertSame(['Manage Tasks / Tasks / Edit'], $result['display']['system_notes'][0]['items']);
    }

    public function test_build_returns_null_when_meta_and_display_are_empty(): void
    {
        $this->assertNull((new AuditLogMetaBuilder())->build());
    }
}
