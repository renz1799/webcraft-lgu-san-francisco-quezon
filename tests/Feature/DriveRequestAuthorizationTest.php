<?php

namespace Tests\Feature;

use App\Core\Http\Requests\Drive\ConnectDriveRequest;
use App\Core\Http\Requests\Drive\DisconnectDriveRequest;
use App\Core\Http\Requests\Drive\UploadDriveFileRequest;
use App\Core\Models\User;
use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Mockery;
use Tests\TestCase;

class DriveRequestAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_connect_request_uses_permission_first_authorization(): void
    {
        $request = ConnectDriveRequest::create('/drive/connect', 'POST');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForPermission('drive_connections.connect', true);

        $this->assertTrue($request->authorize());
    }

    public function test_disconnect_request_uses_permission_first_authorization(): void
    {
        $request = DisconnectDriveRequest::create('/drive/disconnect', 'POST');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForPermission('drive_connections.disconnect', true);

        $this->assertTrue($request->authorize());
    }

    public function test_upload_request_uses_permission_first_authorization(): void
    {
        $request = UploadDriveFileRequest::create('/drive/upload', 'POST');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForPermission('drive_files.create', true);

        $this->assertTrue($request->authorize());
    }

    private function makeUser(string $id): User
    {
        return User::query()->create([
            'id' => $id,
            'username' => $id,
            'email' => "{$id}@example.com",
            'password' => 'secret',
            'is_active' => true,
        ]);
    }

    private function mockAuthorizerForPermission(string $permission, bool $result): void
    {
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(function ($user, string $requestedPermission) use ($permission): bool {
                return $user instanceof User && $requestedPermission === $permission;
            })
            ->andReturn($result);

        app()->instance(AdminContextAuthorizer::class, $authorizer);
    }
}
