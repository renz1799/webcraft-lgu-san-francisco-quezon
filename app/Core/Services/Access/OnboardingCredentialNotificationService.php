<?php

namespace App\Core\Services\Access;

use App\Core\Models\User;
use App\Core\Services\Contracts\Access\OnboardingCredentialNotificationServiceInterface;
use Illuminate\Support\Facades\Password;
use Throwable;

class OnboardingCredentialNotificationService implements OnboardingCredentialNotificationServiceInterface
{
    public function resolveType(User $user, bool $identityCreated = false): string
    {
        if ($identityCreated || $user->shouldReceiveOnboardingInvitation()) {
            return 'invitation';
        }

        return 'access_granted';
    }

    public function send(
        User $user,
        string $moduleName,
        ?string $departmentName = null,
        ?string $roleName = null,
        bool $identityCreated = false,
        bool $membershipActive = true,
    ): array {
        $type = $this->resolveType($user, $identityCreated);

        try {
            if ($type === 'invitation') {
                $token = Password::broker()->createToken($user);

                $user->sendUserInvitationNotification(
                    $token,
                    $moduleName,
                    $departmentName,
                    $roleName,
                );
            } else {
                $user->sendModuleAccessGrantedNotification(
                    $moduleName,
                    $departmentName,
                    $roleName,
                    $membershipActive,
                );
            }

            return [
                'sent' => true,
                'type' => $type,
            ];
        } catch (Throwable) {
            return [
                'sent' => false,
                'type' => $type,
            ];
        }
    }
}
