<?php

namespace Tests\Feature;

use App\Models\GoogleToken;
use App\Repositories\Eloquent\EloquentGoogleTokenRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GoogleTokenRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->nullable();
        });

        Schema::create('google_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->uuid('department_id');
            $table->uuid('connected_by_user_id')->nullable();
            $table->string('provider', 100)->default('google_drive');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['module_id', 'department_id', 'provider'], 'google_tokens_module_department_provider_unique');
        });

        DB::table('modules')->insert([
            ['id' => 'module-1', 'name' => 'Module 1'],
            ['id' => 'module-2', 'name' => 'Module 2'],
        ]);

        DB::table('departments')->insert([
            ['id' => 'department-1', 'name' => 'Department 1'],
            ['id' => 'department-2', 'name' => 'Department 2'],
        ]);

        DB::table('users')->insert([
            ['id' => 'user-1', 'username' => 'user1'],
            ['id' => 'user-2', 'username' => 'user2'],
        ]);
    }

    public function test_context_repository_upserts_and_reads_tokens_by_module_and_department(): void
    {
        $repository = new EloquentGoogleTokenRepository();

        $token = $repository->upsertForContext('module-1', 'department-1', [
            'connected_by_user_id' => 'user-1',
            'access_token' => 'plain-access',
            'refresh_token' => 'plain-refresh',
        ]);

        $this->assertInstanceOf(GoogleToken::class, $token);

        $stored = $repository->findForContext('module-1', 'department-1');

        $this->assertNotNull($stored);
        $this->assertSame('module-1', $stored->module_id);
        $this->assertSame('department-1', $stored->department_id);
        $this->assertSame('user-1', $stored->connected_by_user_id);
        $this->assertSame('plain-access', Crypt::decryptString((string) $stored->access_token));
        $this->assertSame('plain-refresh', Crypt::decryptString((string) $stored->refresh_token));
    }

    public function test_delete_for_context_only_removes_the_requested_scope(): void
    {
        $repository = new EloquentGoogleTokenRepository();

        $repository->upsertForContext('module-1', 'department-1', [
            'connected_by_user_id' => 'user-1',
            'refresh_token' => 'refresh-1',
        ]);

        $repository->upsertForContext('module-2', 'department-2', [
            'connected_by_user_id' => 'user-2',
            'refresh_token' => 'refresh-2',
        ]);

        $repository->deleteForContext('module-1', 'department-1');

        $this->assertNull($repository->findForContext('module-1', 'department-1'));
        $this->assertNotNull($repository->findForContext('module-2', 'department-2'));
    }
}
