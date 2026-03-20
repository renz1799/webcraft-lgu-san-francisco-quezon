<?php

namespace App\Services\Access;

use App\Models\User;
use App\Services\Contracts\Access\LoginLogServiceInterface;
use App\Services\Contracts\Access\UserProfileServiceInterface;
use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit,
        private readonly LoginLogServiceInterface $loginLogs,
    ) {}

    public function getProfileData(User $user): array
    {
        $loginDetails = $this->loginLogs->recentForUser($user, 4);

        return compact('user', 'loginDetails');
    }

    public function updateProfile(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $beforeUser = $user->only(['email', 'username']);
            $beforeProfile = $user->profile?->only([
                'first_name','middle_name','last_name','name_extension','address','contact_details','profile_photo_path'
            ]) ?? [];

            $profileData = [
                'first_name'      => $data['first_name'],
                'middle_name'     => $data['middle_name'] ?? null,
                'last_name'       => $data['last_name'],
                'name_extension'  => $data['name_extension'] ?? null,
                'address'         => $data['address'] ?? null,
                'contact_details' => $data['contact_details'] ?? null,
            ];

            // Photo upload
            if (!empty($data['profile_photo']) && $data['profile_photo'] instanceof UploadedFile) {
                $profileData['profile_photo_path'] = $this->storeProfilePhoto($user, $data['profile_photo']);
            }

            $user->update([
                'email'    => $data['email'],
                'username' => $data['username'],
            ]);

            $user->profile()->updateOrCreate([], $profileData);

            $afterUser = $user->fresh()->only(['email', 'username']);
            $afterProfile = $user->fresh()->profile?->only([
                'first_name','middle_name','last_name','name_extension','address','contact_details','profile_photo_path'
            ]) ?? [];

            $this->audit->record(
                'user.profile.updated',
                $user,
                ['user' => $beforeUser, 'profile' => $beforeProfile],
                ['user' => $afterUser, 'profile' => $afterProfile],
                $this->meta(),
                null,
                $this->buildProfileUpdatedDisplay($user->fresh(), $beforeUser, $beforeProfile, $afterUser, $afterProfile)
            );
        });
    }

    public function updatePassword(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $before = [
                'must_change_password' => (bool) $user->must_change_password,
            ];

            $user->forceFill([
                'password' => Hash::make($data['new_password']),
                'must_change_password' => false, // Clear the flag here.
            ])->save();

            $this->audit->record(
                'user.password.changed',
                $user,
                $before,
                ['must_change_password' => false, 'password_changed' => true],
                $this->meta(),
                'user changed own password',
                $this->buildPasswordChangedDisplay($user)
            );
        });
    }

    private function storeProfilePhoto(User $user, UploadedFile $file): string
    {
        // Delete old if exists
        $old = $user->profile?->profile_photo_path;
        if ($old) {
            Storage::disk('public')->delete($old);
        }

        $filename = $user->id . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('profile_photos', $filename, 'public');
    }

    private function meta(): array
    {
        return [
            'ip' => request()->ip(),
            'ua' => request()->userAgent(),
        ];
    }

    private function buildProfileUpdatedDisplay(
        User $user,
        array $beforeUser,
        array $beforeProfile,
        array $afterUser,
        array $afterProfile
    ): array {
        $fields = [
            'user.email' => 'Email',
            'user.username' => 'Username',
            'profile.first_name' => 'First Name',
            'profile.middle_name' => 'Middle Name',
            'profile.last_name' => 'Last Name',
            'profile.name_extension' => 'Name Extension',
            'profile.address' => 'Address',
            'profile.contact_details' => 'Contact Details',
            'profile.profile_photo_path' => 'Profile Photo',
        ];

        $items = [];
        foreach ($fields as $path => $label) {
            [$group, $key] = explode('.', $path, 2);

            $before = $group === 'user'
                ? ($beforeUser[$key] ?? null)
                : ($beforeProfile[$key] ?? null);
            $after = $group === 'user'
                ? ($afterUser[$key] ?? null)
                : ($afterProfile[$key] ?? null);

            if (($before ?? null) === ($after ?? null)) {
                continue;
            }

            $items[] = [
                'label' => $label,
                'before' => $before ?: 'None',
                'after' => $after ?: 'None',
            ];
        }

        return [
            'summary' => 'Profile updated for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Profile Changes',
                    'items' => $items ?: [[
                        'label' => 'Profile',
                        'value' => 'Profile saved with no tracked field changes.',
                    ]],
                ],
            ],
            'request_details' => [
                'Current Username' => $afterUser['username'] ?? ($user->username ?: 'None'),
                'Current Email' => $afterUser['email'] ?? ($user->email ?: 'None'),
            ],
        ];
    }

    private function buildPasswordChangedDisplay(User $user): array
    {
        return [
            'summary' => 'Password changed for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Password Update',
                    'items' => [
                        [
                            'label' => 'Password',
                            'value' => 'User updated their password successfully.',
                        ],
                        [
                            'label' => 'Password Change Required',
                            'before' => 'Required',
                            'after' => 'Cleared',
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Username' => $user->username ?: 'None',
                'Email' => $user->email ?: 'None',
            ],
        ];
    }

    private function userDisplayName(User $user): string
    {
        $profileName = trim((string) ($user->profile?->full_name ?? ''));
        if ($profileName !== '') {
            return $profileName;
        }

        return (string) ($user->username ?: $user->email ?: 'User');
    }
}

