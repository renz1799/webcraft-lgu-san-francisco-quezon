<?php

namespace Tests\Feature;

use App\Core\Http\Requests\AuditLogs\AuditLogPrintRequest;
use App\Core\Http\Requests\AuditLogs\RestoreSubjectRequest;
use App\Core\Models\User;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\AdminRouteResolver;
use App\Core\Support\CurrentContext;
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
        $user = new User();
        $user->forceFill(['id' => 'actor-print-1']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canViewCurrentContextAuditLogs')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

        $request = AuditLogPrintRequest::create('/audit-logs/print', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_print_request_authorize_denies_user_without_view_access(): void
    {
        $user = new User();
        $user->forceFill(['id' => 'actor-print-2']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canViewCurrentContextAuditLogs')
            ->once()
            ->with($user)
            ->andReturn(false);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

        $request = AuditLogPrintRequest::create('/audit-logs/print', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_restore_request_authorize_allows_admin_alias_role(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 'actor-1']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canRestoreCurrentContextAuditData')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'user',
            'id' => '11111111-1111-1111-1111-111111111111',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_restore_request_authorize_allows_restore_permission(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 'actor-2']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canRestoreCurrentContextAuditData')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'user',
            'id' => '11111111-1111-1111-1111-111111111111',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_restore_request_authorize_denies_user_without_restore_access(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 'actor-3']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canRestoreCurrentContextAuditData')
            ->once()
            ->with($user)
            ->andReturn(false);
        $user->shouldReceive('getRoleNames')
            ->once()
            ->andReturn(collect(['Staff']));
        $user->shouldReceive('getAllPermissions')
            ->once()
            ->andReturn(collect([(object) ['name' => 'audit_logs.view']]));
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

        $request = TestableRestoreSubjectRequest::create('/audit/restore', 'POST', [
            'type' => 'user',
            'id' => '11111111-1111-1111-1111-111111111111',
        ]);
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_restore_request_authorize_denies_cross_module_role_subject(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 'actor-4']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canRestoreCurrentContextAuditData')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

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
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 'actor-5']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canRestoreCurrentContextAuditData')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

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
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 'actor-6']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canRestoreCurrentContextAuditData')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

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

    public function test_restore_request_authorize_denies_user_restore_on_module_scoped_route(): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 'actor-7']);
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('canRestoreCurrentContextAuditData')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->app->instance(AdminContextAuthorizer::class, $authorizer);

        $resolver = Mockery::mock(AdminRouteResolver::class);
        $resolver->shouldReceive('isModuleScoped')
            ->once()
            ->andReturn(true);
        $this->app->instance(AdminRouteResolver::class, $resolver);

        $request = ModuleScopedUserRestoreSubjectRequest::create('/gso/audit/restore', 'POST', [
            'type' => 'user',
            'id' => '11111111-1111-1111-1111-111111111111',
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

class ModuleScopedUserRestoreSubjectRequest extends RestoreSubjectRequest
{
    protected function typeMap(): array
    {
        return [
            'user' => User::class,
        ];
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
