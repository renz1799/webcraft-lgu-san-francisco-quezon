<?php

namespace Tests\Feature;

use App\Core\Repositories\Eloquent\EloquentLoginDetailRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginLogDatatableRepositoryTest extends TestCase
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

    public function test_datatable_returns_only_current_module_rows(): void
    {
        DB::table('users')->insert([
            [
                'id' => 'user-1',
                'username' => 'alpha.staff',
                'email' => 'alpha.staff@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'user-2',
                'username' => 'beta.staff',
                'email' => 'beta.staff@example.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('login_details')->insert([
            [
                'id' => 'log-1',
                'module_id' => 'module-1',
                'user_id' => 'user-1',
                'email' => 'alpha.staff@example.com',
                'success' => false,
                'reason' => 'invalid_password',
                'ip_address' => '127.0.0.1',
                'device' => 'PHPUnit',
                'location' => null,
                'address' => 'Alpha City',
                'latitude' => '14.59950000',
                'longitude' => '120.98420000',
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'id' => 'log-2',
                'module_id' => 'module-2',
                'user_id' => 'user-2',
                'email' => 'beta.staff@example.com',
                'success' => true,
                'reason' => 'ok',
                'ip_address' => '127.0.0.2',
                'device' => 'Browser',
                'location' => null,
                'address' => 'Beta City',
                'latitude' => null,
                'longitude' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $result = (new EloquentLoginDetailRepository())->datatable('module-1', [], 1, 15);

        $this->assertSame(1, $result['total']);
        $this->assertSame('alpha.staff', $result['data'][0]['user']);
        $this->assertSame('invalid_password', $result['data'][0]['reason']);
        $this->assertSame('https://www.google.com/maps?q=14.59950000,120.98420000', $result['data'][0]['location_url']);
    }

    public function test_recent_for_user_returns_only_current_module_rows(): void
    {
        DB::table('login_details')->insert([
            [
                'id' => 'log-10',
                'module_id' => 'module-1',
                'user_id' => 'user-1',
                'email' => 'alpha.staff@example.com',
                'success' => true,
                'reason' => 'ok',
                'ip_address' => '127.0.0.1',
                'device' => 'PHPUnit',
                'location' => null,
                'address' => 'Alpha City',
                'latitude' => null,
                'longitude' => null,
                'created_at' => now()->subMinutes(2),
                'updated_at' => now()->subMinutes(2),
            ],
            [
                'id' => 'log-11',
                'module_id' => 'module-2',
                'user_id' => 'user-1',
                'email' => 'alpha.staff@example.com',
                'success' => false,
                'reason' => 'module_access_denied',
                'ip_address' => '127.0.0.2',
                'device' => 'Browser',
                'location' => null,
                'address' => 'Beta City',
                'latitude' => null,
                'longitude' => null,
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
        ]);

        $result = (new EloquentLoginDetailRepository())->recentForUser('module-1', 'user-1', 4);

        $this->assertCount(1, $result);
        $this->assertSame('log-10', $result->first()->id);
        $this->assertSame('module-1', $result->first()->module_id);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('login_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('email')->nullable();
            $table->boolean('success')->default(false);
            $table->string('reason', 32)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('device')->nullable();
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }
}
