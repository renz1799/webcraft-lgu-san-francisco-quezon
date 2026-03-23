<?php

namespace App\Core\Http\Middleware;

use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Support\CurrentContext;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveModuleContext
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly ModuleAccessServiceInterface $moduleAccess,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($this->context->moduleId()) {
            if ($this->context->usesPlatformContext()) {
                $module = $this->context->module();

                if ($module && session('current_module_id') !== (string) $module->id) {
                    $this->moduleAccess->rememberActiveModule($module);
                }
            }

            return $next($request);
        }

        $accessibleModules = $this->moduleAccess->switchableModulesForUser($user);

        if ($accessibleModules->isEmpty()) {
            $accessibleModules = $this->moduleAccess->accessibleModulesForUser($user);
        }

        if ($accessibleModules->isEmpty()) {
            abort(403, 'No active module access is available for this account.');
        }

        if ($accessibleModules->count() === 1) {
            $module = $accessibleModules->first();

            $this->context->setModule($module);
            $this->moduleAccess->rememberActiveModule($module);

            return $next($request);
        }

        return redirect()->route('modules.index');
    }
}
