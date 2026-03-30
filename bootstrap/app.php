<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register middleware aliases
        $middleware->alias([
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, 
            'role' => \App\Core\Http\Middleware\ContextRoleMiddleware::class,
            'permission' => \App\Core\Http\Middleware\ContextPermissionMiddleware::class,
            'legacy_permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'check.admin.or.permission' => \App\Core\Http\Middleware\CheckAdminOrPermission::class,
            'role_or_permission' => \App\Core\Http\Middleware\RoleOrPermissionMiddleware::class,
            'password.changed' => \App\Core\Http\Middleware\EnsurePasswordChanged::class,
            'active_module' => \App\Core\Http\Middleware\EnsureActiveModuleContext::class,
            'module' => \App\Core\Http\Middleware\SetCurrentModule::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
