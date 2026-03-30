<?php

namespace App\Core\Http\Middleware;

use App\Core\Support\AdminContextAuthorizer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminOrPermission
{
    public function __construct(
        private readonly AdminContextAuthorizer $authorizer,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        if (! $user || ! $this->authorizer->allowsPermission($user, (string) $permission)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
