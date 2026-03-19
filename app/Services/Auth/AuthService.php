<?php

namespace App\Services\Auth;

use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Services\Contracts\Auth\AuthServiceInterface;
use App\Services\Contracts\GeocodingServiceInterface;
use App\Builders\Contracts\Login\LoginAttemptLogBuilderInterface;
use App\Support\CurrentContext;
use Illuminate\Support\Facades\Auth;

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

        if (! $user || ! $user->is_active) {
            return false;
        }

        if (! $user->hasAnyRole(['Administrator', 'Department Head'])) {
            $moduleId = (string) $this->currentContext->moduleId();

            if (! $this->moduleAccess->hasActiveModuleAccess($user, $moduleId)) {
                return false;
            }
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