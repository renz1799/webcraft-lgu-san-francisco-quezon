<?php

namespace App\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only enforce for authenticated users
        if (! $user) {
            return $next($request);
        }

        // Not required, allow.
        if (! (bool) $user->must_change_password) {
            return $next($request);
        }

        // Allow these routes while forced to change password
        $allowedRouteNames = [
            'profile.index',          // GET /profile
            'profile.updatePassword', // PUT /profile/password
            'logout',                 // POST /logout
        ];

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $allowedRouteNames, true)) {
            return $next($request);
        }

        // Allow assets so UI does not break
        if (
            $request->is('build/*') ||
            $request->is('storage/*') ||
            $request->is('favicon.ico') ||
            $request->is('up')
        ) {
            return $next($request);
        }

        return redirect()->route('profile.index', ['tab' => 'account-settings']);
    }
}

