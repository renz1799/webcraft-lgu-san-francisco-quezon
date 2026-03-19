<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\GeocodingServiceInterface;
use App\Services\Contracts\ModuleAccessServiceInterface;
use App\Support\CurrentContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly LoginDetailRepositoryInterface $loginDetails,
        private readonly GeocodingServiceInterface $geocoder,
        private readonly ModuleAccessServiceInterface $moduleAccess,
        private readonly CurrentContext $currentContext,
    ) {}

    public function register(array $data): User
    {
        // ✅ Defense-in-depth: enforce server-side authorization even if routes/requests are gated
        $actor = auth()->user();
        if (! $actor || ! $actor->hasRole('Administrator')) {
            abort(403, 'Only Administratoristrators may create users.');
        }

        // Add a correlation id for the whole flow (optional)
        $reqId = (string) Str::uuid();

        $roleInput = trim((string) ($data['role'] ?? ''));
        $email     = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $username  = trim((string) ($data['username'] ?? ''));

        // ✅ Optional: prevent creating/assigning Administrator role unless actor is Administrator (already true here)
        // Keeps this safe if you later relax request/route gating.
        if ($roleInput === 'Administrator' && ! $actor->hasRole('Administrator')) {
            abort(403, 'You may not assign the Administrator role.');
        }

        return DB::transaction(function () use ($username, $email, $data, $roleInput, $reqId, $actor) {
            $user = $this->users->create([
                'username'              => $username,
                'email'                 => $email,
                'password'              => Hash::make((string) $data['password']), // never log
                'is_active'             => true,
                'must_change_password'  => true, // ✅ strongly recommended for Administrator-created accounts
            ]);

            // ✅ Assign role + sync role defaults (repo handles it)
            $this->users->assignRoleAndSyncPermissions($user, $roleInput);

            // Optional audit/log (sanitized)
            /*
            Log::info('auth.register: user created', [
                'req_id'     => $reqId,
                'actor_id'   => $actor->id,
                'user_id'    => $user->id,
                'role'       => $roleInput,
            ]);
            */

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

                $moduleId = (string) $this->currentContext->moduleId();

        if ($moduleId === '' || ! $this->moduleAccess->hasActiveModuleAccess($user, $moduleId)) {
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
                'reason'     => 'module_access_denied',
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
