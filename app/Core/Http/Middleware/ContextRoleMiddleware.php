<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\AdminContextAuthorizer;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ContextRoleMiddleware
{
    public function __construct(
        private readonly AdminContextAuthorizer $authorizer,
    ) {}

    public function handle($request, Closure $next, $roles)
    {
        $user = Auth::user();

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        $parsedRoles = array_values(array_filter(array_map(
            static fn (string $role): string => trim($role),
            explode('|', (string) $roles)
        )));

        if (! $this->authorizer->hasAnyRole($user, $parsedRoles)) {
            throw UnauthorizedException::forRoles($parsedRoles);
        }

        return $next($request);
    }
}
