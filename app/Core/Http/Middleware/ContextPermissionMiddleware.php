<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\AdminContextAuthorizer;
use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class ContextPermissionMiddleware
{
    public function __construct(
        private readonly AdminContextAuthorizer $authorizer,
    ) {}

    public function handle($request, Closure $next, $permissions)
    {
        $user = Auth::user();

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        $parsedPermissions = array_values(array_filter(array_map(
            static fn (string $permission): string => trim($permission),
            explode('|', (string) $permissions)
        )));

        if (! $this->authorizer->allowsAnyPermission($user, $parsedPermissions)) {
            throw UnauthorizedException::forPermissions($parsedPermissions);
        }

        return $next($request);
    }
}
