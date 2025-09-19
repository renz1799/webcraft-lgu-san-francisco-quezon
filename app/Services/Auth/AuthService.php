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
        $credentials = ['email' => $data['email'], 'password' => $data['password']];
        $remember = (bool) ($data['remember'] ?? false);

        if (! Auth::attempt($credentials, $remember)) {
           /*  Log::warning('Failed login attempt', [
                'email'     => $data['email'],
                'ip'        => $data['ip'],
                'lat'       => $data['latitude'],
                'lng'       => $data['longitude'],
                'device'    => $data['user_agent'],
            ]); */
            return false;
        }

        request()->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // Map URL + address (keep fast; consider async job if API is slow)
        $locationUrl = "https://www.google.com/maps?q={$data['latitude']},{$data['longitude']}";
        $address     = $this->geocoder->reverseGeocode($data['latitude'], $data['longitude']);

        $this->loginDetails->create([
            'user_id'   => $user->id,
            'ip_address'=> $data['ip'],
            'device'    => $data['user_agent'],
            'location'  => $locationUrl,
            'address'   => $address,
            'latitude'  => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

      /*   Log::info('User logged in', [
            'user_id' => $user->id,
            'ip'      => $data['ip'],
        ]); */

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
