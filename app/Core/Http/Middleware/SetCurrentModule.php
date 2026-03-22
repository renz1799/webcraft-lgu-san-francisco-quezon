<?php

namespace App\Core\Http\Middleware;

use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Support\CurrentContext;
use Closure;
use Illuminate\Http\Request;

class SetCurrentModule
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly ModuleAccessServiceInterface $moduleAccess,
    ) {}

    public function handle(Request $request, Closure $next, string $moduleCode)
    {
        $module = $this->moduleAccess->findActiveModuleByCode($moduleCode);

        if (! $module) {
            abort(404);
        }

        $user = $request->user();

        if ($user && ! $this->moduleAccess->hasActiveModuleAccess($user, (string) $module->id)) {
            abort(403, 'You do not have access to this module.');
        }

        $this->context->setModule($module);
        $this->moduleAccess->rememberActiveModule($module);

        return $next($request);
    }
}
