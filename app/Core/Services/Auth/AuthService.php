<?php

namespace App\Core\Services\Auth;

use App\Core\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Auth\AuthServiceInterface;
use App\Core\Services\Contracts\Geocoding\GeocodingServiceInterface;
use App\Core\Builders\Contracts\Login\LoginAttemptLogBuilderInterface;
use App\Core\Support\CurrentContext;
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
        $moduleId = $this->currentContext->moduleId();

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

        /** @var \App\Core\Models\User|null $user */
        $user = $this->users->findByEmail($email);

        if (! $user) {
            $this->recordLoginAttempt(
                moduleId: $moduleId,
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
            );

            return false;
        }

        if (! $user->is_active) {
            $this->recordLoginAttempt(
                moduleId: $moduleId,
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
            );

            return false;
        }

        if (! $this->moduleAccess->hasAnyActiveModuleAccess($user)) {
            $this->recordLoginAttempt(
                moduleId: $moduleId,
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
            );

            return false;
        }

        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $this->recordLoginAttempt(
                moduleId: $moduleId,
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

        $this->recordLoginAttempt(
            moduleId: $moduleId,
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
        );

        return true;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    private function recordLoginAttempt(
        ?string $moduleId,
        ?string $userId,
        string $email,
        ?string $ip,
        ?string $userAgent,
        ?string $locationUrl,
        ?string $address,
        mixed $latitude,
        mixed $longitude,
        bool $success,
        string $reason,
    ): void {
        $this->loginDetails->create(
            $this->loginAttemptLogBuilder->build(
                moduleId: $moduleId,
                userId: $userId,
                email: $email,
                ip: (string) $ip,
                userAgent: $userAgent,
                locationUrl: $locationUrl,
                address: $address,
                latitude: $latitude,
                longitude: $longitude,
                success: $success,
                reason: $reason,
            )
        );
    }
}
