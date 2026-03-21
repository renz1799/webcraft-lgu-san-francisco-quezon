<?php

namespace Tests\Feature;

use App\Core\Builders\AuditLogs\AuditLogPrintReportBuilder;
use App\Core\Models\AuditLog;
use App\Core\Models\Module;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Models\UserProfile;
use Tests\TestCase;

class AuditLogPrintReportBuilderTest extends TestCase
{
    public function test_build_uses_module_relation_and_subject_display_label(): void
    {
        $module = new Module();
        $module->forceFill([
            'id' => '11111111-1111-1111-1111-111111111111',
            'code' => 'ACCESS',
            'name' => 'Access Control',
        ]);

        $profile = new UserProfile();
        $profile->forceFill([
            'id' => '99999999-9999-4999-8999-999999999999',
            'user_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'first_name' => 'Craig',
            'middle_name' => 'Scot',
            'last_name' => 'Schamberger',
            'name_extension' => null,
        ]);

        $actor = new User();
        $actor->forceFill([
            'id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'username' => 'craig.admin',
            'email' => 'craig@example.com',
        ]);
        $actor->setRelation('profile', $profile);

        $role = new Role();
        $role->forceFill([
            'id' => '33333333-3333-4333-8333-333333333333',
            'module_id' => '11111111-1111-1111-1111-111111111111',
            'name' => 'Records Manager',
            'guard_name' => 'web',
        ]);

        $log = new AuditLog();
        $log->forceFill([
            'id' => '55555555-5555-4555-8555-555555555555',
            'module_id' => '11111111-1111-1111-1111-111111111111',
            'actor_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'actor_type' => User::class,
            'subject_type' => Role::class,
            'subject_id' => '33333333-3333-4333-8333-333333333333',
            'action' => 'role.updated',
            'message' => 'Role updated.',
            'meta' => [
                'display' => [
                    'subject_label' => 'Records Manager',
                ],
            ],
        ]);
        $log->created_at = now();
        $log->setRelation('module', $module);
        $log->setRelation('actor', $actor);
        $log->setRelation('subject', $role);

        $report = (new AuditLogPrintReportBuilder())->build(collect([$log]), [
            'module' => 'access',
            'subject_type' => 'role',
        ]);

        $this->assertSame('Access Control', $report->rows[0]['module']);
        $this->assertSame('Craig Scot Schamberger', $report->rows[0]['actor_name']);
        $this->assertSame(
            'Role: Records Manager [33333333-3333-4333-8333-333333333333]',
            $report->rows[0]['subject']
        );
        $this->assertSame('access', $report->filters['module']);
        $this->assertSame('role', $report->filters['subject_type']);
    }
}
