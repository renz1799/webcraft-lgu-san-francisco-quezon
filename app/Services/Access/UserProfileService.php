<?php

namespace App\Services\Access;

use App\Models\User;
use App\Models\LoginDetail;
use App\Services\Contracts\UserProfileServiceInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserProfileService implements UserProfileServiceInterface
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit
    ) {}

    public function getMailSettingsData(User $user): array
    {
        $loginDetails = LoginDetail::where('user_id', $user->id)
            ->latest()
            ->take(4)
            ->get();

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
                $this->meta()
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
                'must_change_password' => false, // ✅ clear the flag here
            ])->save();

            $this->audit->record(
                'user.password.changed',
                $user,
                $before,
                ['must_change_password' => false, 'password_changed' => true],
                $this->meta(),
                'user changed own password'
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
}

