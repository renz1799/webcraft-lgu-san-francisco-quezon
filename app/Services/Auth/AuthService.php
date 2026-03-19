<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\GeocodingServiceInterface;
use App\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Builders\Contracts\Login\LoginAttemptLogBuilderInterface;
use App\Support\CurrentContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly LoginDetailRepositoryInterface $loginDetails,
        private readonly GeocodingServiceInterface $geocoder,
        private readonly ModuleAccessServiceInterface $moduleAccess,
        private readonly CurrentContext $currentContext,
        private readonly LoginAttemptLogBuilderInterface $loginAttemptLogBuilder,
    ) {}

    public function register(array $data): User
    {
        $actor = auth()->user();
        if (! $actor || ! $actor->hasRole('Administrator')) {
            abort(403, 'Only Administratoristrators may create users.');
        }

        $reqId = (string) Str::uuid();

        $roleInput = trim((string) ($data['role'] ?? ''));
        $email     = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $username  = trim((string) ($data['username'] ?? ''));

        if ($roleInput === 'Administrator' && ! $actor->hasRole('Administrator')) {
            abort(403, 'You may not assign the Administrator role.');
        }

        return DB::transaction(function () use ($username, $email, $data, $roleInput, $reqId, $actor) {
            $user = $this->users->create([
                'username'             => $username,
                'email'                => $email,
                'password'             => Hash::make((string) $data['password']),
                'is_active'            => true,
                'must_change_password' => true,
            ]);

            $this->users->assignRoleAndSyncPermissions($user, $roleInput);

            return $user;
        });
    }

    public function attemptLogin(array $data): bool
    {
        $email    = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $remember = (bool) ($data['remember'] ?? false);

        $ip  = $data['ip'] ?? request()->ip();
        $ua  = $data['user_agent'] ?? request()->userAgent();
        $lat = $data['latitude'] ?? null;
        $lng = $data['longitude'] ?? null;

        $hasCoords   = is_numeric($lat) && is_numeric($lng);
        $locationUrl = $hasCoords ? "https://www.google.com/maps?q={$lat},{$lng}" : null;

        $geocodeOnFailure = false;

        $address = null;
        if ($hasCoords && $geocodeOnFailure) {
            try {
                $address = $this->geocoder->reverseGeocode((float) $lat, (float) $lng);
            } catch (\Throwable) {
            }
        }

        /** @var \App\Models\User|null $user */
        $user = $this->users->findByEmail($email);

        if (! $user) {
            $this->loginDetails->create(
                $this->loginAttemptLogBuilder->build(
                    userId: null,
                    email: $email,
                    ip: $ip,
                    userAgent: $ua,
                    locationUrl: $locationUrl,
                    address: $address,
                    latitude: $lat,
                    longitude: $lng,
                    success: false,
                    reason: 'unknown_email',
                )
            );

            return false;
        }

        if (! $user->is_active) {
            $this->loginDetails->create(
                $this->loginAttemptLogBuilder->build(
                    userId: (string) $user->id,
                    email: $email,
                    ip: $ip,
                    userAgent: $ua,
                    locationUrl: $locationUrl,
                    address: $address,
                    latitude: $lat,
                    longitude: $lng,
                    success: false,
                    reason: 'inactive',
                )
            );

            return false;
        }

        if (! Hash::check($password, $user->password)) {
            $this->loginDetails->create(
                $this->loginAttemptLogBuilder->build(
                    userId: (string) $user->id,
                    email: $email,
                    ip: $ip,
                    userAgent: $ua,
                    locationUrl: $locationUrl,
                    address: $address,
                    latitude: $lat,
                    longitude: $lng,
                    success: false,
                    reason: 'invalid_password',
                )
            );

            return false;
        }

        $moduleId = (string) $this->currentContext->moduleId();

        if ($moduleId === '' || ! $this->moduleAccess->hasActiveModuleAccess($user, $moduleId)) {
            $this->loginDetails->create(
                $this->loginAttemptLogBuilder->build(
                    userId: (string) $user->id,
                    email: $email,
                    ip: $ip,
                    userAgent: $ua,
                    locationUrl: $locationUrl,
                    address: $address,
                    latitude: $lat,
                    longitude: $lng,
                    success: false,
                    reason: 'module_access_denied',
                )
            );

            return false;
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $this->loginDetails->create(
                $this->loginAttemptLogBuilder->build(
                    userId: (string) $user->id,
                    email: $email,
                    ip: $ip,
                    userAgent: $ua,
                    locationUrl: $locationUrl,
                    address: $address,
                    latitude: $lat,
                    longitude: $lng,
                    success: false,
                    reason: 'guard_reject',
                )
            );

            return false;
        }

        request()->session()->regenerate();

        if ($hasCoords) {
            try {
                $address = $this->geocoder->reverseGeocode((float) $lat, (float) $lng);
            } catch (\Throwable) {
            }
        }

        $this->loginDetails->create(
            $this->loginAttemptLogBuilder->build(
                userId: (string) Auth::id(),
                email: $email,
                ip: $ip,
                userAgent: $ua,
                locationUrl: $locationUrl,
                address: $address,
                latitude: $lat,
                longitude: $lng,
                success: true,
                reason: 'ok',
            )
        );

        return true;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}