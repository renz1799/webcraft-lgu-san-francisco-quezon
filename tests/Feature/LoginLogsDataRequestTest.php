<?php

namespace Tests\Feature;

use App\Http\Requests\Logs\LoginLogsDataRequest;
use Mockery;
use Tests\TestCase;

class LoginLogsDataRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_authorize_allows_view_login_logs_permission(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(false);
        $user->shouldReceive('can')
            ->once()
            ->with('view Login Logs')
            ->andReturn(true);

        $request = LoginLogsDataRequest::create('/login-logs/data', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_denies_user_without_role_or_permission(): void
    {
        $user = Mockery::mock();
        $user->shouldReceive('hasAnyRole')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(false);
        $user->shouldReceive('can')
            ->once()
            ->with('view Login Logs')
            ->andReturn(false);

        $request = LoginLogsDataRequest::create('/login-logs/data', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->assertFalse($request->authorize());
    }

    public function test_validated_normalizes_blank_values_and_applies_defaults(): void
    {
        $request = new TestableLoginLogsDataRequest();
        $request->initialize([
            'search' => '  alpha  ',
            'user' => '   ',
            'size' => '   ',
            'date_from' => '2026-03-20',
        ]);

        $request->prepareForTest();

        $validator = validator()->make($request->all(), $request->rules());
        $this->assertTrue($validator->passes());

        $request->setValidator($validator);
        $validated = $request->validated();

        $this->assertSame('alpha', $validated['search']);
        $this->assertNull($validated['user']);
        $this->assertSame('2026-03-20', $validated['date_from']);
        $this->assertSame(1, $validated['page']);
        $this->assertSame(15, $validated['size']);
    }

    public function test_validation_rejects_date_to_before_date_from(): void
    {
        $request = new TestableLoginLogsDataRequest();
        $request->initialize([
            'date_from' => '2026-03-20',
            'date_to' => '2026-03-19',
        ]);

        $request->prepareForTest();

        $validator = validator()->make($request->all(), $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('date_to', $validator->errors()->toArray());
    }
}

class TestableLoginLogsDataRequest extends LoginLogsDataRequest
{
    public function prepareForTest(): void
    {
        $this->prepareForValidation();
    }
}
