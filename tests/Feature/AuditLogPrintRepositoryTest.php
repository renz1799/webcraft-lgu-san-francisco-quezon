<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Repositories\Eloquent\EloquentAuditLogRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditLogPrintRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    public function test_find_for_print_filters_by_module_actor_and_subject_type_using_real_fields(): void
    {
        DB::table('modules')->insert([
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'code' => 'ACCESS',
                'name' => 'Access Control',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'code' => 'TASKS',
                'name' => 'Task Management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('users')->insert([
            [
                'id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'username' => 'craig.admin',
                'email' => 'craig@example.com',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                'username' => 'jamie.staff',
                'email' => 'jamie@example.com',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('user_profiles')->insert([
            [
                'id' => '99999999-9999-4999-8999-999999999999',
                'user_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'first_name' => 'Craig',
                'middle_name' => 'Scot',
                'last_name' => 'Schamberger',
                'name_extension' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('roles')->insert([
            [
                'id' => '33333333-3333-4333-8333-333333333333',
                'module_id' => '11111111-1111-1111-1111-111111111111',
                'name' => 'Records Manager',
                'guard_name' => 'web',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '44444444-4444-4444-8444-444444444444',
                'module_id' => '22222222-2222-2222-2222-222222222222',
                'name' => 'Task Reviewer',
                'guard_name' => 'web',
                'deleted_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('audit_logs')->insert([
            [
                'id' => '55555555-5555-4555-8555-555555555555',
                'module_id' => '11111111-1111-1111-1111-111111111111',
                'department_id' => null,
                'actor_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'actor_type' => \App\Models\User::class,
                'subject_type' => Role::class,
                'subject_id' => '33333333-3333-4333-8333-333333333333',
                'action' => 'role.updated',
                'message' => 'Role updated.',
                'request_method' => 'POST',
                'request_url' => 'https://example.test/access/roles/33333333-3333-4333-8333-333333333333',
                'ip' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'changes_old' => json_encode(['name' => 'Records Staff']),
                'changes_new' => json_encode(['name' => 'Records Manager']),
                'meta' => json_encode(['display' => ['subject_label' => 'Records Manager']]),
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'id' => '66666666-6666-4666-8666-666666666666',
                'module_id' => '22222222-2222-2222-2222-222222222222',
                'department_id' => null,
                'actor_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                'actor_type' => \App\Models\User::class,
                'subject_type' => Role::class,
                'subject_id' => '44444444-4444-4444-8444-444444444444',
                'action' => 'role.updated',
                'message' => 'Task role updated.',
                'request_method' => 'POST',
                'request_url' => 'https://example.test/tasks/roles/44444444-4444-4444-8444-444444444444',
                'ip' => '127.0.0.2',
                'user_agent' => 'PHPUnit',
                'changes_old' => null,
                'changes_new' => null,
                'meta' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '77777777-7777-4777-8777-777777777777',
                'module_id' => '11111111-1111-1111-1111-111111111111',
                'department_id' => null,
                'actor_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                'actor_type' => \App\Models\User::class,
                'subject_type' => \App\Models\User::class,
                'subject_id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                'action' => 'user.updated',
                'message' => 'User updated.',
                'request_method' => 'PATCH',
                'request_url' => 'https://example.test/access/users/bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                'ip' => '127.0.0.3',
                'user_agent' => 'Browser',
                'changes_old' => null,
                'changes_new' => null,
                'meta' => null,
                'created_at' => now()->subSeconds(30),
                'updated_at' => now()->subSeconds(30),
            ],
        ]);

        $result = (new EloquentAuditLogRepository())->findForPrint([
            'module' => 'access',
            'actor_id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'subject_type' => 'role',
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('55555555-5555-4555-8555-555555555555', $result->first()->id);
        $this->assertSame('Access Control', $result->first()->module->name);
        $this->assertSame(Role::class, $result->first()->subject_type);
        $this->assertSame('Craig Scot Schamberger', $result->first()->actor->profile->full_name);
    }

    private function createSchema(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name_extension')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->string('name')->nullable();
            $table->string('guard_name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('actor_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->string('action')->nullable();
            $table->text('message')->nullable();
            $table->string('request_method')->nullable();
            $table->text('request_url')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('changes_old')->nullable();
            $table->text('changes_new')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();
        });
    }
}
