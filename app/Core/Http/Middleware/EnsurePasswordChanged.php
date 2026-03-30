<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\ProfileRouteResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function __construct(
        private readonly ProfileRouteResolver $profileRoutes,
    ) {}

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
        $allowedRoutePatterns = [
            'profile.index',          // GET /profile
            'profile.updatePassword', // PUT /profile/password
            '*.profile.index',
            '*.profile.updatePassword',
            'logout',                 // POST /logout
        ];

        $routeName = $request->route()?->getName();
        if (
            $routeName
            && collect($allowedRoutePatterns)->contains(
                fn (string $pattern): bool => Str::is($pattern, $routeName)
            )
        ) {
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

        return redirect()->to($this->profileRoutes->accountSettingsUrl($user));
    }
}
