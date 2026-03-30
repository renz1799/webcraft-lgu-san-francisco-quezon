<?php

namespace Tests\Feature;

use App\Core\Builders\Contracts\User\UserPlatformAccessOverviewBuilderInterface;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Access\UserAccessService;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\CurrentContext;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class UserAccessServiceAuditDisplayTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_permissions_synced_display_payload_is_human_friendly(): void
    {
        $users = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'id' => 'user-1',
            'username' => 'imani.blick',
            'email' => 'cordelia52@example.net',
        ]);
        $user->setRelation('profile', (object) ['full_name' => 'Craig Scot Schamberger']);

        $roleAssignments->shouldReceive('roles')
            ->once()
            ->with($user)
            ->andReturn(collect([new Role(['name' => 'Staff'])]));

        $overviewBuilder = Mockery::mock(UserPlatformAccessOverviewBuilderInterface::class);
        $overviewBuilder->shouldIgnoreMissing();

        $service = new UserAccessService($users, $audit, $context, $roleAssignments, $overviewBuilder);

        $method = new ReflectionMethod($service, 'buildPermissionsSyncedDisplay');
        $method->setAccessible(true);

        $display = $method->invoke(
            $service,
            $user,
            ['tasks.view'],
            ['tasks.view', 'tasks.update', 'tasks.archive'],
            [
                ['pKey' => 'manage tasks', 'rKey' => 'tasks', 'aKey' => 'update'],
            ],
            []
        );

        $this->assertSame('Permissions updated for Craig Scot Schamberger', $display['summary']);
        $this->assertSame('Craig Scot Schamberger', $display['subject_label']);
        $this->assertSame('Direct Permissions', $display['sections'][0]['title']);
        $this->assertSame('Added', $display['sections'][0]['items'][0]['label']);
        $this->assertSame(['Tasks / Update', 'Tasks / Archive'], $display['sections'][0]['items'][0]['value']);
        $this->assertSame('Removed', $display['sections'][0]['items'][1]['label']);
        $this->assertSame([], $display['sections'][0]['items'][1]['value']);
        $this->assertSame('Staff', $display['request_details']['Role']);
        $this->assertSame(3, $display['request_details']['Direct Permission Count']);
        $this->assertSame('Resolved selections', $display['system_notes'][0]['title']);
        $this->assertSame(['Manage Tasks / Tasks / Update'], $display['system_notes'][0]['items']);
    }

    public function test_role_and_status_display_payloads_are_human_friendly(): void
    {
        $users = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $roleAssignments = Mockery::mock(ModuleRoleAssignmentServiceInterface::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'id' => 'user-2',
            'username' => 'admin.user',
            'email' => 'admin@example.net',
        ]);
        $user->setRelation('profile', (object) ['full_name' => 'System Reta Administrator V']);

        $roleAssignments->shouldReceive('roles')
            ->once()
            ->with($user)
            ->andReturn(collect([new Role(['name' => 'Administrator'])]));

        $overviewBuilder = Mockery::mock(UserPlatformAccessOverviewBuilderInterface::class);
        $overviewBuilder->shouldIgnoreMissing();

        $service = new UserAccessService($users, $audit, $context, $roleAssignments, $overviewBuilder);

        $roleMethod = new ReflectionMethod($service, 'buildRoleChangedDisplay');
        $roleMethod->setAccessible(true);
        $roleDisplay = $roleMethod->invoke($service, $user, 'Staff', 'Administrator');

        $statusMethod = new ReflectionMethod($service, 'buildStatusUpdatedDisplay');
        $statusMethod->setAccessible(true);
        $statusDisplay = $statusMethod->invoke($service, $user, false, true);

        $this->assertSame('Role updated for System Reta Administrator V', $roleDisplay['summary']);
        $this->assertSame('Staff', $roleDisplay['sections'][0]['items'][0]['before']);
        $this->assertSame('Administrator', $roleDisplay['sections'][0]['items'][0]['after']);
        $this->assertSame('Administrator', $roleDisplay['request_details']['Current Role']);

        $this->assertSame('Status updated for System Reta Administrator V', $statusDisplay['summary']);
        $this->assertSame('Inactive', $statusDisplay['sections'][0]['items'][0]['before']);
        $this->assertSame('Active', $statusDisplay['sections'][0]['items'][0]['after']);
        $this->assertSame('Administrator', $statusDisplay['request_details']['Current Role']);
    }
}
