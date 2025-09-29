<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\GeocodingServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly LoginDetailRepositoryInterface $loginDetails,
        private readonly GeocodingServiceInterface $geocoder,
    ) {}

    public function register(array $data): User
    {
        // add a correlation id for the whole flow
        $reqId = (string) Str::uuid();
      //  Log::withContext(['req_id' => $reqId, 'op' => 'auth.register']);

        $roleInput = trim((string) ($data['role'] ?? ''));
        $email     = mb_strtolower(trim($data['email']));
        $username  = trim($data['username']);

    /*    Log::info('Register: incoming payload (sanitized)', [
            'username'     => $username,
            'email'        => $email,
            'role_raw'     => $roleInput,
            'role_is_uuid' => Str::isUuid($roleInput),
        ]); */

        return DB::transaction(function () use ($username, $email, $data, $roleInput) {
            $user = $this->users->create([
                'username'  => $username,
                'email'     => $email,
                'password'  => Hash::make($data['password']), // never log
                'is_active' => true,
            ]);

          /*  Log::info('Register: user created', [
                'user_id'  => $user->id,
                'key_type' => $user->getKeyType(), // should be "string"
            ]); */

            try {
                $this->users->assignRoleAndSyncPermissions($user, $roleInput);
                /* Log::info('Register: role assignment succeeded', [
                    'user_id'   => $user->id,
                    'role_input'=> $roleInput,
                ]); */
            } catch (\Throwable $e) {
                /* Log::error('Register: role assignment failed', [
                    'user_id'    => $user->id,
                    'role_input' => $roleInput,
                    'message'    => $e->getMessage(),
                ]);*/
                throw $e; // keep behavior the same
            }

            return $user;
        });
    }
    public function attemptLogin(array $data): bool
    {
        // Normalize inputs
        $email    = mb_strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        $remember = (bool)($data['remember'] ?? false);

        // Context (with sensible fallbacks)
        $ip  = $data['ip']         ?? request()->ip();
        $ua  = $data['user_agent'] ?? request()->userAgent();
        $lat = $data['latitude']   ?? null;
        $lng = $data['longitude']  ?? null;

        // Build location fields once so we can attach them to ALL logs
        $hasCoords   = is_numeric($lat) && is_numeric($lng);
        $locationUrl = $hasCoords ? "https://www.google.com/maps?q={$lat},{$lng}" : null;

        // If you also want a human address on failures, set this to true
        $geocodeOnFailure = false;

        $address = null;
        if ($hasCoords && $geocodeOnFailure) {
            try { $address = $this->geocoder->reverseGeocode((float)$lat, (float)$lng); } catch (\Throwable) {}
        }

        /** @var \App\Models\User|null $user */
        $user = $this->users->findByEmail($email);

        if (! $user) {
            // Unknown email
            $this->loginDetails->create([
                'user_id'    => null,
                'email'      => $email,
                'ip_address' => $ip,
                'device'     => $ua,
                'location'   => $locationUrl,
                'address'    => $address,
                'latitude'   => $lat,
                'longitude'  => $lng,
                'success'    => false,
                'reason'     => 'unknown_email',
            ]);
            return false;
        }

        if (! $user->is_active) {
            $this->loginDetails->create([
                'user_id'    => $user->id,
                'email'      => $email,
                'ip_address' => $ip,
                'device'     => $ua,
                'location'   => $locationUrl,
                'address'    => $address,
                'latitude'   => $lat,
                'longitude'  => $lng,
                'success'    => false,
                'reason'     => 'inactive',
            ]);
            return false;
        }

        if (! \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
            $this->loginDetails->create([
                'user_id'    => $user->id,
                'email'      => $email,
                'ip_address' => $ip,
                'device'     => $ua,
                'location'   => $locationUrl,
                'address'    => $address,
                'latitude'   => $lat,
                'longitude'  => $lng,
                'success'    => false,
                'reason'     => 'invalid_password',
            ]);
            return false;
        }

        // Credentials valid — let Laravel log them in
        if (! \Illuminate\Support\Facades\Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $this->loginDetails->create([
                'user_id'    => $user->id,
                'email'      => $email,
                'ip_address' => $ip,
                'device'     => $ua,
                'location'   => $locationUrl,
                'address'    => $address,
                'latitude'   => $lat,
                'longitude'  => $lng,
                'success'    => false,
                'reason'     => 'guard_reject',
            ]);
            return false;
        }

        // Logged in
        request()->session()->regenerate();

        // Optional: only now do a reverse-geocode to avoid slowing failures
        if ($hasCoords) {
            try { $address = $this->geocoder->reverseGeocode((float)$lat, (float)$lng); } catch (\Throwable) {}
        }

        $this->loginDetails->create([
            'user_id'    => \Illuminate\Support\Facades\Auth::id(),
            'email'      => $email,
            'ip_address' => $ip,
            'device'     => $ua,
            'location'   => $locationUrl,
            'address'    => $address,
            'latitude'   => $lat,
            'longitude'  => $lng,
            'success'    => true,
            'reason'     => 'ok',
        ]);

        return true;
    }



    public function logout(): void
    {
        $userId = Auth::id();
      //  Log::info('User logged out', ['user_id' => $userId]);

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
