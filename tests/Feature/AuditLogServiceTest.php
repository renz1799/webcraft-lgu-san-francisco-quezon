<?php

namespace Tests\Feature;

use App\Core\Builders\AuditLogs\AuditLogMetaBuilder;
use App\Core\Models\AuditLog;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\Access\UserModuleDepartmentResolverInterface;
use App\Core\Services\AuditLogs\AuditLogService;
use App\Core\Support\AuditRequestContextResolver;
use App\Core\Support\CurrentContext;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request as RequestFacade;
use Mockery;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_record_stores_structured_display_in_meta_without_breaking_existing_fields(): void
    {
        $logs = Mockery::mock(AuditLogRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $moduleDepartments = Mockery::mock(ModuleDepartmentResolverInterface::class);
        $userModuleDepartments = Mockery::mock(UserModuleDepartmentResolverInterface::class);

        $actor = new User();
        $actor->forceFill(['id' => 'actor-1', 'username' => 'actor.user']);

        $subject = new User();
        $subject->forceFill(['id' => 'subject-1', 'username' => 'subject.user']);

        $request = Mockery::mock(HttpRequest::class);
        $request->shouldReceive('setUserResolver')->andReturnSelf();
        $request->shouldReceive('user')->andReturn($actor);
        $request->shouldReceive('method')->andReturn('POST');
        $request->shouldReceive('ip')->andReturn('127.0.0.1');
        $request->shouldReceive('header')->with('User-Agent')->andReturn('PHPUnit Agent');
        $request->shouldReceive('fullUrl')->andReturn('https://example.test/access/users/subject-1?tab=permissions');

        $this->app->instance('request', $request);
        RequestFacade::swap($request);

        $context->shouldReceive('moduleId')->once()->andReturn('module-1');
        $userModuleDepartments->shouldReceive('resolveForUser')->once()->with('actor-1', 'module-1')->andReturn('department-1');
        $moduleDepartments->shouldReceive('resolveForModule')->never();

        $logs->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                $this->assertSame('module-1', $payload['module_id']);
                $this->assertSame('department-1', $payload['department_id']);
                $this->assertSame('actor-1', $payload['actor_id']);
                $this->assertSame(User::class, $payload['actor_type']);
                $this->assertSame(User::class, $payload['subject_type']);
                $this->assertSame('subject-1', $payload['subject_id']);
                $this->assertSame('user.permissions.synced', $payload['action']);
                $this->assertSame('Permissions updated.', $payload['message']);
                $this->assertSame('POST', $payload['request_method']);
                $this->assertSame('https://example.test/access/users/subject-1?tab=permissions', $payload['request_url']);
                $this->assertSame('127.0.0.1', $payload['ip']);
                $this->assertSame('PHPUnit Agent', $payload['user_agent']);
                $this->assertSame(['direct_permissions' => []], $payload['changes_old']);
                $this->assertSame(['direct_permissions' => ['tasks.update']], $payload['changes_new']);
                $this->assertSame('users.edit', $payload['meta']['source']);
                $this->assertSame('Permissions updated for Craig Scot Schamberger', $payload['meta']['display']['summary']);
                $this->assertSame('Craig Scot Schamberger', $payload['meta']['display']['subject_label']);
                $this->assertSame('Direct Permissions', $payload['meta']['display']['sections'][0]['title']);
                $this->assertSame('Added', $payload['meta']['display']['sections'][0]['items'][0]['label']);
                $this->assertSame(['Tasks / Update'], $payload['meta']['display']['sections'][0]['items'][0]['value']);
                $this->assertSame('Reference No', array_key_first($payload['meta']['display']['request_details']));
                $this->assertSame('PO-2026-0312', $payload['meta']['display']['request_details']['Reference No']);
                $this->assertSame('Resolved selections', $payload['meta']['display']['system_notes'][0]['title']);
                $this->assertSame(['Manage Tasks / Tasks / Update'], $payload['meta']['display']['system_notes'][0]['items']);

                return true;
            }))
            ->andReturn(new AuditLog());

        $service = new AuditLogService(
            $logs,
            $context,
            $moduleDepartments,
            $userModuleDepartments,
            new AuditLogMetaBuilder(),
            new AuditRequestContextResolver(),
        );

        $service->record(
            action: 'user.permissions.synced',
            subject: $subject,
            changesOld: ['direct_permissions' => []],
            changesNew: ['direct_permissions' => ['tasks.update']],
            meta: ['source' => 'users.edit'],
            message: 'Permissions updated.',
            display: [
                'summary' => 'Permissions updated for Craig Scot Schamberger',
                'subject_label' => 'Craig Scot Schamberger',
                'sections' => [
                    [
                        'title' => 'Direct Permissions',
                        'items' => [
                            ['label' => 'Added', 'value' => ['Tasks / Update']],
                            ['label' => 'Removed', 'value' => []],
                        ],
                    ],
                ],
                'request_details' => [
                    'Reference No' => 'PO-2026-0312',
                ],
                'system_notes' => [
                    [
                        'title' => 'Resolved selections',
                        'items' => ['Manage Tasks / Tasks / Update'],
                    ],
                ],
            ],
        );
    }

    public function test_record_without_display_keeps_meta_shape_unchanged(): void
    {
        $logs = Mockery::mock(AuditLogRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $moduleDepartments = Mockery::mock(ModuleDepartmentResolverInterface::class);
        $userModuleDepartments = Mockery::mock(UserModuleDepartmentResolverInterface::class);

        $request = Mockery::mock(HttpRequest::class);
        $request->shouldReceive('setUserResolver')->andReturnSelf();
        $request->shouldReceive('user')->andReturn(null);
        $request->shouldReceive('method')->andReturn('GET');
        $request->shouldReceive('ip')->andReturn(null);
        $request->shouldReceive('header')->with('User-Agent')->andReturn('');
        $request->shouldReceive('fullUrl')->andReturn('https://example.test/audit-logs');
        $this->app->instance('request', $request);
        RequestFacade::swap($request);

        $context->shouldReceive('moduleId')->once()->andReturn(null);
        $userModuleDepartments->shouldReceive('resolveForUser')->once()->with(null, null)->andReturn(null);
        $moduleDepartments->shouldReceive('resolveForModule')->once()->with(null)->andReturn(null);

        $logs->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                $this->assertNull($payload['module_id']);
                $this->assertNull($payload['department_id']);
                $this->assertSame(['source' => 'audit.restore.service'], $payload['meta']);

                return true;
            }))
            ->andReturn(new AuditLog());

        $service = new AuditLogService(
            $logs,
            $context,
            $moduleDepartments,
            $userModuleDepartments,
            new AuditLogMetaBuilder(),
            new AuditRequestContextResolver(),
        );

        $service->record(
            action: 'user.restored',
            meta: ['source' => 'audit.restore.service'],
        );
    }
}
