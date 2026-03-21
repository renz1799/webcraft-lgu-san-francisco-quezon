<?php

namespace Tests\Feature;

use App\Core\Models\User;
use App\Core\Services\Access\UserProfileService;
use App\Core\Services\Contracts\Access\LoginLogServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class UserProfileServiceAuditDisplayTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_profile_updated_display_highlights_changed_fields(): void
    {
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $loginLogs = Mockery::mock(LoginLogServiceInterface::class);

        $user = new User();
        $user->forceFill([
            'id' => 'user-1',
            'username' => 'imani.blick',
            'email' => 'cordelia52@example.net',
        ]);
        $user->setRelation('profile', (object) ['full_name' => 'Craig Scot Schamberger']);

        $service = new UserProfileService($audit, $loginLogs);
        $method = new ReflectionMethod($service, 'buildProfileUpdatedDisplay');
        $method->setAccessible(true);

        $display = $method->invoke(
            $service,
            $user,
            ['email' => 'old@example.net', 'username' => 'imani.blick'],
            ['first_name' => 'Craig', 'last_name' => 'Schamberg', 'profile_photo_path' => null],
            ['email' => 'cordelia52@example.net', 'username' => 'imani.blick'],
            ['first_name' => 'Craig', 'last_name' => 'Schamberger', 'profile_photo_path' => 'profile_photos/user-1.png']
        );

        $this->assertSame('Profile updated for Craig Scot Schamberger', $display['summary']);
        $this->assertSame('Profile Changes', $display['sections'][0]['title']);
        $this->assertSame('Email', $display['sections'][0]['items'][0]['label']);
        $this->assertSame('old@example.net', $display['sections'][0]['items'][0]['before']);
        $this->assertSame('cordelia52@example.net', $display['sections'][0]['items'][0]['after']);
        $this->assertSame('Last Name', $display['sections'][0]['items'][1]['label']);
        $this->assertSame('Schamberg', $display['sections'][0]['items'][1]['before']);
        $this->assertSame('Schamberger', $display['sections'][0]['items'][1]['after']);
        $this->assertSame('Profile Photo', $display['sections'][0]['items'][2]['label']);
        $this->assertSame('None', $display['sections'][0]['items'][2]['before']);
        $this->assertSame('profile_photos/user-1.png', $display['sections'][0]['items'][2]['after']);
    }
}
