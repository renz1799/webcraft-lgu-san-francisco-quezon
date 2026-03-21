<?php

namespace Tests\Feature;

use App\Core\Builders\Login\LoginAttemptLogBuilder;
use App\Core\Models\LoginDetail;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Auth\AuthService;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Geocoding\GeocodingServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class AuthServiceLoginLogTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_invalid_password_login_log_records_reason_and_module_id(): void
    {
        $this->bindRequestWithSession();

        $users = Mockery::mock(UserRepositoryInterface::class);
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $geocoder = Mockery::mock(GeocodingServiceInterface::class);
        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 'user-1';
        $user->email = 'staff@example.com';
        $user->is_active = true;

        $users->shouldReceive('findByEmail')
            ->once()
            ->with('staff@example.com')
            ->andReturn($user);

        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'Department Head'])
            ->andReturn(false);

        $moduleAccess->shouldReceive('hasActiveModuleAccess')
            ->once()
            ->with($user, 'module-1')
            ->andReturn(true);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'staff@example.com', 'password' => 'secret123'], false)
            ->andReturn(false);

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $loginDetails->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['module_id'] === 'module-1'
                    && $payload['user_id'] === 'user-1'
                    && $payload['email'] === 'staff@example.com'
                    && $payload['success'] === false
                    && $payload['reason'] === 'invalid_password';
            }))
            ->andReturnUsing(fn (array $payload) => new LoginDetail($payload));

        $service = new AuthService(
            $users,
            $loginDetails,
            $geocoder,
            $moduleAccess,
            $context,
            new LoginAttemptLogBuilder()
        );

        $result = $service->attemptLogin([
            'email' => 'staff@example.com',
            'password' => 'secret123',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertFalse($result);
    }

    public function test_successful_login_log_records_current_module_id(): void
    {
        $this->bindRequestWithSession();

        $users = Mockery::mock(UserRepositoryInterface::class);
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $geocoder = Mockery::mock(GeocodingServiceInterface::class);
        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 'user-1';
        $user->email = 'staff@example.com';
        $user->is_active = true;

        $users->shouldReceive('findByEmail')
            ->once()
            ->with('staff@example.com')
            ->andReturn($user);

        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'Department Head'])
            ->andReturn(false);

        $moduleAccess->shouldReceive('hasActiveModuleAccess')
            ->once()
            ->with($user, 'module-1')
            ->andReturn(true);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'staff@example.com', 'password' => 'secret123'], false)
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->once()
            ->andReturn('user-1');

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $loginDetails->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['module_id'] === 'module-1'
                    && $payload['user_id'] === 'user-1'
                    && $payload['email'] === 'staff@example.com'
                    && $payload['success'] === true
                    && $payload['reason'] === 'ok';
            }))
            ->andReturnUsing(fn (array $payload) => new LoginDetail($payload));

        $service = new AuthService(
            $users,
            $loginDetails,
            $geocoder,
            $moduleAccess,
            $context,
            new LoginAttemptLogBuilder()
        );

        $result = $service->attemptLogin([
            'email' => 'staff@example.com',
            'password' => 'secret123',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertTrue($result);
    }

    public function test_unknown_email_login_log_records_reason_and_module_id(): void
    {
        $this->bindRequestWithSession();

        $users = Mockery::mock(UserRepositoryInterface::class);
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $geocoder = Mockery::mock(GeocodingServiceInterface::class);
        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $users->shouldReceive('findByEmail')
            ->once()
            ->with('missing@example.com')
            ->andReturn(null);

        Auth::shouldReceive('attempt')->never();

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $loginDetails->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['module_id'] === 'module-1'
                    && $payload['user_id'] === null
                    && $payload['email'] === 'missing@example.com'
                    && $payload['success'] === false
                    && $payload['reason'] === 'unknown_email';
            }))
            ->andReturnUsing(fn (array $payload) => new LoginDetail($payload));

        $service = new AuthService(
            $users,
            $loginDetails,
            $geocoder,
            $moduleAccess,
            $context,
            new LoginAttemptLogBuilder()
        );

        $result = $service->attemptLogin([
            'email' => 'missing@example.com',
            'password' => 'secret123',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertFalse($result);
    }

    public function test_inactive_user_login_log_records_reason_and_module_id(): void
    {
        $this->bindRequestWithSession();

        $users = Mockery::mock(UserRepositoryInterface::class);
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $geocoder = Mockery::mock(GeocodingServiceInterface::class);
        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 'user-2';
        $user->email = 'inactive@example.com';
        $user->is_active = false;

        $users->shouldReceive('findByEmail')
            ->once()
            ->with('inactive@example.com')
            ->andReturn($user);

        Auth::shouldReceive('attempt')->never();

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $loginDetails->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['module_id'] === 'module-1'
                    && $payload['user_id'] === 'user-2'
                    && $payload['email'] === 'inactive@example.com'
                    && $payload['success'] === false
                    && $payload['reason'] === 'inactive';
            }))
            ->andReturnUsing(fn (array $payload) => new LoginDetail($payload));

        $service = new AuthService(
            $users,
            $loginDetails,
            $geocoder,
            $moduleAccess,
            $context,
            new LoginAttemptLogBuilder()
        );

        $result = $service->attemptLogin([
            'email' => 'inactive@example.com',
            'password' => 'secret123',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertFalse($result);
    }

    public function test_module_access_denial_login_log_records_reason_and_module_id(): void
    {
        $this->bindRequestWithSession();

        $users = Mockery::mock(UserRepositoryInterface::class);
        $loginDetails = Mockery::mock(LoginDetailRepositoryInterface::class);
        $geocoder = Mockery::mock(GeocodingServiceInterface::class);
        $moduleAccess = Mockery::mock(ModuleAccessServiceInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 'user-3';
        $user->email = 'staff@example.com';
        $user->is_active = true;

        $users->shouldReceive('findByEmail')
            ->once()
            ->with('staff@example.com')
            ->andReturn($user);

        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'Department Head'])
            ->andReturn(false);

        $moduleAccess->shouldReceive('hasActiveModuleAccess')
            ->once()
            ->with($user, 'module-1')
            ->andReturn(false);

        Auth::shouldReceive('attempt')->never();

        $context->shouldReceive('moduleId')
            ->once()
            ->andReturn('module-1');

        $loginDetails->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return $payload['module_id'] === 'module-1'
                    && $payload['user_id'] === 'user-3'
                    && $payload['email'] === 'staff@example.com'
                    && $payload['success'] === false
                    && $payload['reason'] === 'module_access_denied';
            }))
            ->andReturnUsing(fn (array $payload) => new LoginDetail($payload));

        $service = new AuthService(
            $users,
            $loginDetails,
            $geocoder,
            $moduleAccess,
            $context,
            new LoginAttemptLogBuilder()
        );

        $result = $service->attemptLogin([
            'email' => 'staff@example.com',
            'password' => 'secret123',
            'ip' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertFalse($result);
    }

    private function bindRequestWithSession(): void
    {
        $request = Request::create('/login', 'POST');
        $session = new Store('testing', new ArraySessionHandler(120));
        $session->start();
        $request->setLaravelSession($session);

        $this->app->instance('request', $request);
    }
}
