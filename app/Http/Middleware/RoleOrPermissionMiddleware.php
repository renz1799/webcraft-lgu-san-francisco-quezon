<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $rolesOrPermissions)
    {
        $user = Auth::user();

        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        $rolesAndPermissions = explode('|', $rolesOrPermissions);

        $hasAccess = collect($rolesAndPermissions)->some(function ($roleOrPermission) use ($user) {
            if ($user->hasRole($roleOrPermission)) {
                return true;
            }
            if ($user->can($roleOrPermission)) {
                return true;
            }
            return false;
        });

        if (!$hasAccess) {
            throw UnauthorizedException::forRolesOrPermissions($rolesAndPermissions);
        }

        return $next($request);
    }
}
