<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckAdminOrPermission
{
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

        // Check if the user has the 'admin' role or the specified permission
        if (!$user || (!$user->hasRole('admin') && !$user->can($permission))) {
            // Log the failure for debugging
            Log::info('User failed middleware check', [
                'user_id' => Auth::id(),
                'roles' => $user ? $user->roles->pluck('name') : null,
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : null,
                'required_permission' => $permission,
            ]);

            // Abort with a 403 error
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
