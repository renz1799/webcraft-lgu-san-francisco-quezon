<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\AdminContextAuthorizer;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    public function __construct(
        private readonly AdminContextAuthorizer $authorizer,
    ) {}

    public function handle($request, Closure $next, $rolesOrPermissions)
    {
        $user = Auth::user();

        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        $rolesAndPermissions = array_values(array_filter(array_map(
            static fn (string $token): string => trim($token),
            explode('|', (string) $rolesOrPermissions)
        )));

        $hasAccess = $this->authorizer->allowsAny($user, $rolesAndPermissions);

        if (!$hasAccess) {
            throw UnauthorizedException::forRolesOrPermissions($rolesAndPermissions);
        }

        return $next($request);
    }
}
