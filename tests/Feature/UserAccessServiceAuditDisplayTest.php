<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Access\UserAccessService;
use App\Services\Contracts\AuditLogServiceInterface;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

        $rolesRelation = Mockery::mock(BelongsToMany::class);
        $rolesRelation->shouldReceive('pluck')
            ->once()
            ->with('name')
            ->andReturn(collect(['Staff']));

        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'id' => 'user-1',
            'username' => 'imani.blick',
            'email' => 'cordelia52@example.net',
        ]);
        $user->setRelation('profile', (object) ['full_name' => 'Craig Scot Schamberger']);
        $user->shouldReceive('roles')->andReturn($rolesRelation);

        $service = new UserAccessService($users, $audit);

        $method = new ReflectionMethod($service, 'buildPermissionsSyncedDisplay');
        $method->setAccessible(true);

        $display = $method->invoke(
            $service,
            $user,
            ['view Tasks'],
            ['view Tasks', 'modify Tasks', 'delete Tasks'],
            [
                ['pKey' => 'manage tasks', 'rKey' => 'tasks', 'aKey' => 'edit'],
            ],
            []
        );

        $this->assertSame('Permissions updated for Craig Scot Schamberger', $display['summary']);
        $this->assertSame('Craig Scot Schamberger', $display['subject_label']);
        $this->assertSame('Direct Permissions', $display['sections'][0]['title']);
        $this->assertSame('Added', $display['sections'][0]['items'][0]['label']);
        $this->assertSame(['Modify Tasks', 'Delete Tasks'], $display['sections'][0]['items'][0]['value']);
        $this->assertSame('Removed', $display['sections'][0]['items'][1]['label']);
        $this->assertSame([], $display['sections'][0]['items'][1]['value']);
        $this->assertSame('Staff', $display['request_details']['Role']);
        $this->assertSame(3, $display['request_details']['Direct Permission Count']);
        $this->assertSame('Resolved selections', $display['system_notes'][0]['title']);
        $this->assertSame(['Manage Tasks / Tasks / Edit'], $display['system_notes'][0]['items']);
    }

    public function test_role_and_status_display_payloads_are_human_friendly(): void
    {
        $users = Mockery::mock(UserRepositoryInterface::class);
        $audit = Mockery::mock(AuditLogServiceInterface::class);

        $rolesRelation = Mockery::mock(BelongsToMany::class);
        $rolesRelation->shouldReceive('pluck')
            ->once()
            ->with('name')
            ->andReturn(collect(['Administrator']));

        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill([
            'id' => 'user-2',
            'username' => 'admin.user',
            'email' => 'admin@example.net',
        ]);
        $user->setRelation('profile', (object) ['full_name' => 'System Reta Administrator V']);
        $user->shouldReceive('roles')->andReturn($rolesRelation);

        $service = new UserAccessService($users, $audit);

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
