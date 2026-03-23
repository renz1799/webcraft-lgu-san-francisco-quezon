<?php

namespace App\Core\Services\Access;

use App\Core\Data\Users\ModuleUserOnboardingData;
use App\Core\Models\Department;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Models\UserModule;
use App\Core\Models\UserProfile;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\Access\ModuleUserOnboardingServiceInterface;
use App\Core\Services\Contracts\Access\OnboardingCredentialNotificationServiceInterface;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ModuleUserOnboardingService implements ModuleUserOnboardingServiceInterface
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly ModuleDepartmentResolverInterface $moduleDepartments,
        private readonly OnboardingCredentialNotificationServiceInterface $credentialNotifications,
        private readonly ModuleRoleAssignmentServiceInterface $roleAssignments,
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function getCreateData(): array
    {
        $module = $this->requireModule();
        $defaultDepartmentId = $this->moduleDepartments->defaultDepartmentIdForModule((string) $module->id);
        $defaultDepartment = $defaultDepartmentId
            ? Department::query()->find($defaultDepartmentId)
            : null;

        return [
            'onboardingMode' => 'module',
            'currentModule' => $module,
            'moduleId' => (string) $module->id,
            'roles' => $this->rolesForModule((string) $module->id),
            'departmentId' => $defaultDepartmentId,
            'departmentLabel' => $defaultDepartment?->name
                ?: ($module->defaultDepartment?->name ?: 'Module Default Department'),
        ];
    }

    public function onboard(ModuleUserOnboardingData $data): array
    {
        $module = $this->requireModule();
        $moduleId = (string) $module->id;
        $moduleName = $this->moduleDisplayName();
        $targetRole = $this->resolveRole($moduleId, $data->role);
        $resolvedDepartmentId = $this->moduleDepartments->defaultDepartmentIdForModule($moduleId);
        $resolvedDepartmentName = $this->departmentName($resolvedDepartmentId);
        $existingUser = User::query()
            ->withTrashed()
            ->with('profile')
            ->where('email', $data->email)
            ->first();

        if ($existingUser?->trashed()) {
            throw ValidationException::withMessages([
                'email' => 'This platform account is archived in Core Platform. Ask Core Platform to restore it before onboarding.',
            ]);
        }

        if ($existingUser instanceof User && ! (bool) $existingUser->is_active) {
            throw ValidationException::withMessages([
                'email' => 'This platform account is inactive in Core Platform. Ask Core Platform to reactivate it before onboarding.',
            ]);
        }

        $result = DB::transaction(function () use ($existingUser, $data, $moduleId, $resolvedDepartmentId, $targetRole): array {
            $identityCreated = false;

            if ($existingUser instanceof User) {
                $user = $existingUser;
            } else {
                $user = $this->createPlatformIdentity($data, $resolvedDepartmentId);
                $identityCreated = true;
            }

            $membership = UserModule::query()
                ->where('user_id', (string) $user->getKey())
                ->where('module_id', $moduleId)
                ->first();

            $currentRoleName = $this->roleAssignments->roles($user)->first()?->name;
            $beforeStatus = $membership ? (bool) $membership->is_active : null;
            $beforeDepartmentId = $membership?->department_id;
            $membershipCreated = false;
            $membershipUpdated = false;

            if (! $membership) {
                $membership = $this->createMembership($user, $moduleId, $resolvedDepartmentId, $data->isActive);
                $membershipCreated = true;
            } elseif ($this->membershipNeedsUpdate($membership, $resolvedDepartmentId, $data->isActive)) {
                $this->updateMembership($membership, $resolvedDepartmentId, $data->isActive);
                $membershipUpdated = true;
            }

            $roleChanged = $currentRoleName !== $targetRole->name;

            if ($roleChanged) {
                $this->roleAssignments->sync($user, [$targetRole]);
            }

            return [
                'user' => $user->fresh(['profile']),
                'identity_created' => $identityCreated,
                'membership_created' => $membershipCreated,
                'membership_updated' => $membershipUpdated,
                'role_changed' => $roleChanged,
                'before_status' => $beforeStatus,
                'before_department_id' => $beforeDepartmentId,
                'before_role' => $currentRoleName,
                'after_role' => $targetRole->name,
                'after_status' => $data->isActive,
                'after_department_id' => $resolvedDepartmentId,
            ];
        });

        if (! $result['identity_created'] && ! $result['membership_created'] && ! $result['membership_updated'] && ! $result['role_changed']) {
            return [
                'status' => 'noop',
                'user' => $result['user'],
                'message' => $this->buildNoopMessage($moduleName, $data->email),
            ];
        }

        $notification = $this->credentialNotifications->send(
            user: $result['user'],
            moduleName: $moduleName,
            departmentName: $resolvedDepartmentName,
            roleName: $result['after_role'],
            identityCreated: (bool) $result['identity_created'],
            membershipActive: (bool) $result['after_status'],
        );

        $this->recordOnboardingAudit(
            user: $result['user'],
            moduleName: $moduleName,
            beforeRole: $result['before_role'],
            afterRole: $result['after_role'],
            beforeStatus: $result['before_status'],
            afterStatus: (bool) $result['after_status'],
            beforeDepartmentId: $result['before_department_id'],
            afterDepartmentId: $result['after_department_id'],
            identityCreated: (bool) $result['identity_created'],
            membershipCreated: (bool) $result['membership_created'],
            membershipUpdated: (bool) $result['membership_updated'],
            roleChanged: (bool) $result['role_changed'],
            notificationType: (string) $notification['type'],
            credentialEmailSent: (bool) $notification['sent'],
        );

        if ($notification['sent']) {
            $this->recordCredentialNotificationAudit(
                user: $result['user'],
                notificationType: (string) $notification['type'],
                moduleName: $moduleName,
                departmentName: $resolvedDepartmentName,
                roleName: $result['after_role'],
                isActive: (bool) $result['after_status'],
            );
        }

        return [
            'status' => $this->resolveResultStatus($result),
            'user' => $result['user'],
            'message' => $this->buildSuccessMessage(
                moduleName: $moduleName,
                email: (string) $result['user']->email,
                identityCreated: (bool) $result['identity_created'],
                membershipCreated: (bool) $result['membership_created'],
                afterStatus: (bool) $result['after_status'],
                notificationType: (string) $notification['type'],
                notificationSent: (bool) $notification['sent'],
            ),
        ];
    }

    private function createPlatformIdentity(ModuleUserOnboardingData $data, ?string $departmentId): User
    {
        $user = User::query()->create([
            'primary_department_id' => $departmentId,
            'username' => $this->generateAvailableUsername($data->email),
            'email' => $data->email,
            'password' => Hash::make(Str::random(40)),
            'user_type' => 'Viewer',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        UserProfile::query()->updateOrCreate(
            ['user_id' => (string) $user->getKey()],
            [
                'first_name' => $data->firstName,
                'middle_name' => $data->middleName,
                'last_name' => $data->lastName,
                'name_extension' => $data->nameExtension,
            ]
        );

        return $user;
    }

    private function createMembership(User $user, string $moduleId, ?string $departmentId, bool $isActive): UserModule
    {
        return UserModule::query()->create([
            'user_id' => (string) $user->getKey(),
            'module_id' => $moduleId,
            'department_id' => $departmentId,
            'is_active' => $isActive,
            'granted_at' => $isActive ? now() : null,
            'revoked_at' => $isActive ? null : now(),
        ]);
    }

    private function updateMembership(UserModule $membership, ?string $departmentId, bool $isActive): void
    {
        $membership->forceFill([
            'department_id' => $departmentId,
            'is_active' => $isActive,
            'granted_at' => $isActive ? now() : $membership->granted_at,
            'revoked_at' => $isActive ? null : now(),
        ])->save();
    }

    private function membershipNeedsUpdate(UserModule $membership, ?string $departmentId, bool $isActive): bool
    {
        return (string) ($membership->department_id ?? '') !== (string) ($departmentId ?? '')
            || (bool) $membership->is_active !== $isActive;
    }

    private function rolesForModule(string $moduleId)
    {
        return Role::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function resolveRole(string $moduleId, string $roleName): Role
    {
        $role = Role::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->where('name', trim($roleName))
            ->first();

        if (! $role) {
            throw ValidationException::withMessages([
                'role' => 'Select a valid module role for this onboarding request.',
            ]);
        }

        return $role;
    }

    private function requireModule()
    {
        $module = $this->context->module();

        if (! $module || $module->isPlatformContext()) {
            throw ValidationException::withMessages([
                'module' => 'Module-assisted onboarding is only available inside a business or support module context.',
            ]);
        }

        return $module;
    }

    private function moduleDisplayName(): string
    {
        $module = $this->requireModule();
        $name = trim((string) ($module->name ?: $module->code ?: 'Module'));

        return $name !== '' ? $name : 'Module';
    }

    private function departmentName(?string $departmentId): string
    {
        if (! $departmentId) {
            return 'Module Default Department';
        }

        return (string) (Department::query()->whereKey($departmentId)->value('name') ?: 'Department');
    }

    private function generateAvailableUsername(string $email): string
    {
        $base = Str::of((string) Str::before($email, '@'))
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9._-]+/', '.')
            ->trim('.')
            ->value();

        $base = $base !== '' ? $base : 'staff';
        $base = Str::limit($base, 48, '');
        $candidate = $base;
        $suffix = 2;

        while (User::query()->withTrashed()->where('username', $candidate)->exists()) {
            $candidate = Str::limit($base, 44, '') . '.' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function recordOnboardingAudit(
        User $user,
        string $moduleName,
        ?string $beforeRole,
        ?string $afterRole,
        ?bool $beforeStatus,
        bool $afterStatus,
        ?string $beforeDepartmentId,
        ?string $afterDepartmentId,
        bool $identityCreated,
        bool $membershipCreated,
        bool $membershipUpdated,
        bool $roleChanged,
        string $notificationType,
        bool $credentialEmailSent,
    ): void {
        $this->audit->record(
            'user.module_onboarding.completed',
            $user,
            [
                'identity_created' => false,
                'membership_created' => false,
                'membership_status' => $beforeStatus,
                'department_id' => $beforeDepartmentId,
                'role' => $beforeRole,
            ],
            [
                'identity_created' => $identityCreated,
                'membership_created' => $membershipCreated,
                'membership_updated' => $membershipUpdated,
                'role_changed' => $roleChanged,
                'membership_status' => $afterStatus,
                'department_id' => $afterDepartmentId,
                'role' => $afterRole,
                'credential_notification_type' => $notificationType,
                'credential_email_sent' => $credentialEmailSent,
            ],
            [
                'trigger' => 'module_assisted_onboarding',
                'channel' => $credentialEmailSent ? 'email' : 'system',
                'notification_type' => $notificationType,
            ],
            'Staff member onboarded through module-assisted onboarding.',
            [
                'summary' => 'Staff onboarded to ' . $moduleName . ': ' . $this->userDisplayName($user),
                'subject_label' => $this->userDisplayName($user),
                'sections' => [
                    [
                        'title' => 'Identity',
                        'items' => [
                            [
                                'label' => 'Platform Account',
                                'value' => $identityCreated ? 'Created during onboarding' : 'Existing platform identity reused',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Module Access',
                        'items' => [
                            [
                                'label' => 'Module',
                                'value' => $moduleName,
                            ],
                            [
                                'label' => 'Department',
                                'before' => $this->departmentName($beforeDepartmentId),
                                'after' => $this->departmentName($afterDepartmentId),
                            ],
                            [
                                'label' => 'Access Status',
                                'before' => $beforeStatus === null ? 'Not Assigned' : ($beforeStatus ? 'Active' : 'Inactive'),
                                'after' => $afterStatus ? 'Active' : 'Inactive',
                            ],
                            [
                                'label' => 'Role',
                                'before' => $beforeRole ?: 'No Role Assigned',
                                'after' => $afterRole ?: 'No Role Assigned',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Credential Email',
                        'items' => [
                            [
                                'label' => 'Delivery',
                                'value' => $credentialEmailSent
                                    ? $this->notificationAuditValue($notificationType)
                                    : 'The onboarding email could not be sent automatically.',
                            ],
                        ],
                    ],
                ],
                'request_details' => [
                    'Email' => $user->email ?: 'None',
                    'Username' => $user->username ?: 'None',
                ],
            ]
        );
    }

    private function recordCredentialNotificationAudit(
        User $user,
        string $notificationType,
        string $moduleName,
        string $departmentName,
        ?string $roleName,
        bool $isActive,
    ): void
    {
        if ($notificationType === 'invitation') {
            $expiryMinutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 30);

            $this->audit->record(
                'auth.invitation.sent',
                $user,
                [],
                [
                    'email' => $user->email,
                    'module' => $moduleName,
                    'department' => $departmentName,
                    'role' => $roleName,
                ],
                [
                    'channel' => 'email',
                    'broker' => config('auth.defaults.passwords'),
                    'trigger' => 'module_assisted_onboarding',
                    'expires_in_minutes' => $expiryMinutes,
                    'notification_type' => 'invitation',
                ],
                'Invitation email sent during module-assisted onboarding.',
                [
                    'summary' => 'Invitation email sent for ' . ($user->email ?: 'user'),
                    'subject_label' => $this->userDisplayName($user),
                    'sections' => [
                        [
                            'title' => 'Invitation',
                            'items' => [
                                [
                                    'label' => 'Module',
                                    'value' => $moduleName,
                                ],
                                [
                                    'label' => 'Department',
                                    'value' => $departmentName,
                                ],
                                [
                                    'label' => 'Role',
                                    'value' => $roleName ?: 'No Role Assigned',
                                ],
                                [
                                    'label' => 'Expires In',
                                    'value' => $expiryMinutes . ' minutes',
                                ],
                            ],
                        ],
                    ],
                    'request_details' => [
                        'Email' => $user->email ?: 'None',
                    ],
                ]
            );

            return;
        }

        $this->audit->record(
            'access.module_access_granted.sent',
            $user,
            [],
            [
                'email' => $user->email,
                'module' => $moduleName,
                'department' => $departmentName,
                'role' => $roleName,
                'membership_status' => $isActive,
            ],
            [
                'channel' => 'email',
                'trigger' => 'module_assisted_onboarding',
                'notification_type' => 'access_granted',
            ],
            'Module access notification email sent during module-assisted onboarding.',
            [
                'summary' => 'Module access email sent for ' . ($user->email ?: 'user'),
                'subject_label' => $this->userDisplayName($user),
                'sections' => [
                    [
                        'title' => 'Module Access',
                        'items' => [
                            [
                                'label' => 'Module',
                                'value' => $moduleName,
                            ],
                            [
                                'label' => 'Department',
                                'value' => $departmentName,
                            ],
                            [
                                'label' => 'Role',
                                'value' => $roleName ?: 'No Role Assigned',
                            ],
                            [
                                'label' => 'Status',
                                'value' => $isActive ? 'Active' : 'Inactive',
                            ],
                        ],
                    ],
                ],
                'request_details' => [
                    'Email' => $user->email ?: 'None',
                ],
            ]
        );
    }

    private function resolveResultStatus(array $result): string
    {
        if ($result['identity_created']) {
            return 'created';
        }

        if ($result['membership_created']) {
            return 'attached';
        }

        return 'updated';
    }

    private function buildSuccessMessage(
        string $moduleName,
        string $email,
        bool $identityCreated,
        bool $membershipCreated,
        bool $afterStatus,
        string $notificationType,
        bool $notificationSent,
    ): string {
        if ($identityCreated) {
            $message = 'Platform account created and added to ' . $moduleName . '.';
        } elseif ($membershipCreated) {
            $message = 'Existing platform account added to ' . $moduleName . '.';
        } else {
            $message = 'Existing ' . $moduleName . ' assignment updated.';
        }

        if ($notificationSent) {
            $message .= ' ' . $this->successEmailSentence($notificationType, $email);
        } else {
            $message .= ' The staff member was onboarded, but the onboarding email could not be sent automatically.';
        }

        if (! $afterStatus) {
            $message .= ' Module access remains inactive until you enable it.';
        }

        return $message;
    }

    private function buildNoopMessage(string $moduleName, string $email): string
    {
        return $email . ' is already assigned to ' . $moduleName . ' with the selected role and access status.';
    }

    private function userDisplayName(User $user): string
    {
        $profileName = trim((string) ($user->profile?->full_name ?? ''));

        if ($profileName !== '') {
            return $profileName;
        }

        return (string) ($user->username ?: $user->email ?: 'User');
    }

    private function notificationAuditValue(string $notificationType): string
    {
        return $notificationType === 'invitation'
            ? 'Invitation email sent for first-time password setup.'
            : 'Module access email sent to an existing platform user.';
    }

    private function successEmailSentence(string $notificationType, string $email): string
    {
        return $notificationType === 'invitation'
            ? 'An invitation email was sent to ' . $email . ' so the user can set a password.'
            : 'A module access email was sent to ' . $email . ' with sign-in instructions.';
    }
}
