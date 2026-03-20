<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Access\UserProfileService;
use App\Services\Contracts\Access\LoginLogServiceInterface;
use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use Mockery;
use Tests\TestCase;

class UserProfileServiceLoginDetailsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_profile_data_uses_login_log_service_for_recent_details(): void
    {
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $loginLogs = Mockery::mock(LoginLogServiceInterface::class);

        $user = new User([
            'id' => 'user-1',
            'username' => 'alpha.staff',
            'email' => 'alpha.staff@example.com',
        ]);

        $recentLogs = collect([
            new \App\Models\LoginDetail(['id' => 'log-1']),
            new \App\Models\LoginDetail(['id' => 'log-2']),
        ]);

        $loginLogs->shouldReceive('recentForUser')
            ->once()
            ->with($user, 4)
            ->andReturn($recentLogs);

        $service = new UserProfileService($audit, $loginLogs);

        $result = $service->getProfileData($user);

        $this->assertSame($user, $result['user']);
        $this->assertSame($recentLogs, $result['loginDetails']);
    }
}
