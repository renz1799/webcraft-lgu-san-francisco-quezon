<?php

namespace Tests\Feature;

use App\Http\Requests\AuditLogs\AuditLogPrintRequest;
use App\Http\Requests\AuditLogs\RestoreSubjectRequest;
use App\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mockery;
use Tests\TestCase;

class AuditLogAccessRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_print_request_authorize_allows_view_audit_logs_permission(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(false);
        $user->shouldReceive('can')
            ->once()
            ->with('view Audit Logs')
            ->andReturn(true);

        $request = AuditLogPrintRequest::create('/audit-logs/print', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_print_request_authorize_denies_user_without_view_access(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(false);
        $user->shouldReceive('can')
            ->once()
            ->with('view Audit Logs')
            ->andReturn(false);

        $request = AuditLogPrintRequest::create('/audit-logs/print', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_restore_request_authorize_allows_admin_alias_role(): void
    {
        $user = Mockery::mock();
        $user->id = 'actor-1';
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(true);
        $user->shouldReceive('can')->never();

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'user',
            'id' => '11111111-1111-1111-1111-111111111111',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_restore_request_authorize_allows_restore_permission(): void
    {
        $user = Mockery::mock();
        $user->id = 'actor-2';
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(false);
        $user->shouldReceive('can')
            ->once()
            ->with('modify Allow Data Restoration')
            ->andReturn(true);

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'user',
            'id' => '11111111-1111-1111-1111-111111111111',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_restore_request_authorize_denies_user_without_restore_access(): void
    {
        $user = Mockery::mock();
        $user->id = 'actor-3';
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(false);
        $user->shouldReceive('can')
            ->once()
            ->with('modify Allow Data Restoration')
            ->andReturn(false);
        $user->shouldReceive('getRoleNames')
            ->once()
            ->andReturn(collect(['Staff']));
        $user->shouldReceive('getAllPermissions')
            ->once()
            ->andReturn(collect([(object) ['name' => 'view Audit Logs']]));

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'user',
            'id' => '11111111-1111-1111-1111-111111111111',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_restore_request_authorize_denies_cross_module_role_subject(): void
    {
        $user = Mockery::mock();
        $user->id = 'actor-4';
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(true);
        $user->shouldReceive('can')->never();

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');
        $this->app->instance(CurrentContext::class, $context);

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'role',
            'id' => 'role-2',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_restore_request_model_returns_current_module_permission_subject(): void
    {
        $user = Mockery::mock();
        $user->id = 'actor-5';
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(true);
        $user->shouldReceive('can')->never();

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')
            ->twice()
            ->andReturn('module-1');
        $this->app->instance(CurrentContext::class, $context);

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'permission',
            'id' => 'permission-1',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());

        $model = $request->model();

        $this->assertInstanceOf(TestModuleScopedPermissionAuditSubject::class, $model);
        $this->assertSame('module-1', $model->module_id);
    }

    public function test_restore_request_authorize_denies_module_scoped_subject_without_current_module(): void
    {
        $user = Mockery::mock();
        $user->id = 'actor-6';
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(true);
        $user->shouldReceive('can')->never();

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn(null);
        $this->app->instance(CurrentContext::class, $context);

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'permission',
            'id' => 'permission-1',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }
}

class TestableRestoreSubjectRequest extends RestoreSubjectRequest
{
    protected function typeMap(): array
    {
        return [
            'user' => TestSoftDeletableAuditSubject::class,
            'permission' => TestModuleScopedPermissionAuditSubject::class,
            'role' => TestModuleScopedRoleAuditSubject::class,
        ];
    }

    protected function requiresModuleScope(string $class): bool
    {
        return in_array($class, [
            TestModuleScopedPermissionAuditSubject::class,
            TestModuleScopedRoleAuditSubject::class,
        ], true);
    }
}

class TestSoftDeletableAuditSubject
{
    use SoftDeletes;

    public static function withTrashed(): object
    {
        return new class
        {
            public function whereKey(string $id): object
            {
                return $this;
            }

            public function exists(): bool
            {
                return true;
            }

            public function findOrFail(string $id): TestSoftDeletableAuditSubject
            {
                return new TestSoftDeletableAuditSubject();
            }
        };
    }
}

class TestModuleScopedPermissionAuditSubject
{
    use SoftDeletes;

    public function __construct(
        public string $id,
        public string $module_id,
    ) {}

    public static function withTrashed(): TestFakeAuditSubjectQuery
    {
        return new TestFakeAuditSubjectQuery([
            new self('permission-1', 'module-1'),
            new self('permission-2', 'module-2'),
        ]);
    }
}

class TestModuleScopedRoleAuditSubject
{
    use SoftDeletes;

    public function __construct(
        public string $id,
        public string $module_id,
    ) {}

    public static function withTrashed(): TestFakeAuditSubjectQuery
    {
        return new TestFakeAuditSubjectQuery([
            new self('role-1', 'module-1'),
            new self('role-2', 'module-2'),
        ]);
    }
}

class TestFakeAuditSubjectQuery
{
    private ?string $key = null;
    private ?string $moduleId = null;

    /**
     * @param  array<int, object>  $records
     */
    public function __construct(
        private readonly array $records,
    ) {}

    public function whereKey(string $id): self
    {
        $this->key = $id;

        return $this;
    }

    public function where(string $column, string $value): self
    {
        if ($column === 'module_id') {
            $this->moduleId = $value;
        }

        return $this;
    }

    public function exists(): bool
    {
        return $this->match() !== null;
    }

    public function findOrFail(string $id): object
    {
        $this->whereKey($id);

        $record = $this->match();
        if ($record === null) {
            throw new ModelNotFoundException();
        }

        return $record;
    }

    private function match(): ?object
    {
        foreach ($this->records as $record) {
            if ($this->key !== null && $record->id !== $this->key) {
                continue;
            }

            if ($this->moduleId !== null && (($record->module_id ?? null) !== $this->moduleId)) {
                continue;
            }

            return $record;
        }

        return null;
    }
}
