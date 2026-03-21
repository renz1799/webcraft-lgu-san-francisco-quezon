<?php

namespace Tests\Feature;

use App\Core\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Core\Services\Access\LoginLogService;
use App\Core\Support\CurrentContext;
use Mockery;
use Tests\TestCase;

class LoginLogServiceModuleScopeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_datatable_uses_current_module_context(): void
    {
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $loginDetails->shouldReceive('datatable')
            ->once()
            ->with('module-1', [
                'search' => 'alpha',
                'status' => 'failed',
            ], 2, 25)
            ->andReturn([
                'data' => [['id' => 'log-1']],
                'last_page' => 3,
                'total' => 1,
                'recordsTotal' => 1,
                'recordsFiltered' => 1,
            ]);

        $service = new LoginLogService($loginDetails, $context);

        $result = $service->datatable([
            'page' => 2,
            'size' => 25,
            'search' => 'alpha',
            'status' => 'failed',
        ]);

        $this->assertSame(1, $result['total']);
        $this->assertSame('log-1', $result['data'][0]['id']);
    }

    public function test_datatable_returns_empty_payload_without_current_module(): void
    {
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn(null);

        $loginDetails->shouldNotReceive('datatable');

        $service = new LoginLogService($loginDetails, $context);

        $result = $service->datatable([
            'page' => 1,
            'size' => 15,
            'search' => 'alpha',
        ]);

        $this->assertSame([], $result['data']);
        $this->assertSame(0, $result['total']);
        $this->assertSame(0, $result['recordsTotal']);
        $this->assertSame(0, $result['recordsFiltered']);
        $this->assertSame(1, $result['last_page']);
    }

    public function test_recent_for_user_uses_current_module_context(): void
    {
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $user = new \App\Core\Models\User();
        $user->forceFill(['id' => 'user-1']);
        $logs = collect([new \App\Core\Models\LoginDetail(['id' => 'log-1'])]);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $loginDetails->shouldReceive('recentForUser')
            ->once()
            ->with('module-1', 'user-1', 4)
            ->andReturn($logs);

        $service = new LoginLogService($loginDetails, $context);

        $this->assertSame($logs, $service->recentForUser($user, 4));
    }

    public function test_recent_for_user_returns_empty_collection_without_current_module(): void
    {
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $user = new \App\Core\Models\User();
        $user->forceFill(['id' => 'user-1']);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn(null);

        $loginDetails->shouldNotReceive('recentForUser');

        $service = new LoginLogService($loginDetails, $context);

        $this->assertCount(0, $service->recentForUser($user, 4));
    }
}
