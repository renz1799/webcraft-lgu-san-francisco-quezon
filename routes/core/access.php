<?php

use App\Core\Http\Controllers\Access\PermissionController;
use App\Core\Http\Controllers\Access\RolesController;
use App\Core\Http\Controllers\Access\CoreUserOnboardingController;
use App\Core\Http\Controllers\Access\UserIdentityChangeRequestController;
use App\Core\Http\Controllers\Access\UserAccessController;
use App\Core\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])->group(function () {
    Route::middleware('permission:users.create')->group(function () {
        Route::get('/sign-up', [AuthController::class, 'showSignUpForm'])->name('sign-up');
        Route::get('/register/options', [AuthController::class, 'registrationOptions'])->name('register.options');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
    });

    Route::middleware('permission:identity_change_requests.view')->group(function () {
        Route::get('/identity-change-requests', [UserIdentityChangeRequestController::class, 'index'])
            ->name('identity-change-requests.index');
        Route::prefix('identity-change-requests')
            ->whereUuid(['identityChangeRequest'])
            ->group(function () {
                Route::get('{identityChangeRequest}', [UserIdentityChangeRequestController::class, 'show'])
                    ->name('identity-change-requests.show');
            });
    });

    Route::prefix('identity-change-requests')
        ->whereUuid(['identityChangeRequest'])
        ->group(function () {
            Route::post('{identityChangeRequest}/approve', [UserIdentityChangeRequestController::class, 'approve'])
                ->middleware('permission:identity_change_requests.approve')
                ->name('identity-change-requests.approve');
            Route::post('{identityChangeRequest}/reject', [UserIdentityChangeRequestController::class, 'reject'])
                ->middleware('permission:identity_change_requests.reject')
                ->name('identity-change-requests.reject');
        });

    Route::middleware('permission:users.view_access|users.manage_access')->group(function () {
        Route::get('/users/data', [UserAccessController::class, 'data'])->name('access.users.data');
        Route::get('/users', [UserAccessController::class, 'index'])->name('access.users.index');

        // Backward-compatible aliases while callers migrate off the legacy URI.
        Route::get('/users/permissions/data', [UserAccessController::class, 'data'])->name('legacy.access.users.data');
        Route::get('/users/permissions', [UserAccessController::class, 'index'])->name('legacy.access.users.index');

        Route::prefix('users')
            ->whereUuid(['user'])
            ->group(function () {
                Route::get('{user}/permissions', [UserAccessController::class, 'show'])->name('access.users.show');
                Route::get('{user}/access-overview', [UserAccessController::class, 'accessOverview'])->name('access.users.access-overview');
            });
    });

    Route::middleware('permission:users.create')->group(function () {
        Route::get('/users/create', [CoreUserOnboardingController::class, 'create'])->name('access.users.create');
        Route::post('/users', [CoreUserOnboardingController::class, 'store'])->name('access.users.store');
    });

    Route::prefix('users')
        ->whereUuid(['user'])
        ->group(function () {
            Route::get('{user}/permissions/edit', [UserAccessController::class, 'edit'])
                ->middleware('permission:users.manage_access')
                ->name('access.users.edit');
            Route::patch('{user}/permissions', [UserAccessController::class, 'updateModulePermissions'])
                ->middleware('permission:users.manage_access')
                ->name('access.users.update');
            Route::patch('{user}/status', [UserAccessController::class, 'updateStatus'])
                ->middleware('permission:users.deactivate')
                ->name('access.users.status.update');
            Route::post('{user}/reset-password', [UserAccessController::class, 'resetPassword'])
                ->middleware('permission:users.reset_password')
                ->name('access.users.password.reset');
            Route::delete('{user}', [UserAccessController::class, 'destroy'])
                ->middleware('permission:users.deactivate')
                ->name('access.users.destroy');
            Route::patch('{user}/restore', [UserAccessController::class, 'restore'])
                ->middleware('permission:users.restore')
                ->name('access.users.restore');
        });

    Route::middleware('permission:roles.view|roles.create|roles.update|roles.archive|roles.restore')->group(function () {
        Route::get('/roles/data', [RolesController::class, 'data'])->name('access.roles.data');
        Route::get('/roles', [RolesController::class, 'index'])->name('access.roles.index');
    });

    Route::get('/roles/create', [RolesController::class, 'create'])
        ->middleware('permission:roles.create')
        ->name('access.roles.create');
    Route::post('/roles', [RolesController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('access.roles.store');
    Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])
        ->whereUuid('role')
        ->middleware('permission:roles.update')
        ->name('access.roles.edit');
    Route::match(['put', 'patch'], '/roles/{role}', [RolesController::class, 'update'])
        ->whereUuid('role')
        ->middleware('permission:roles.update')
        ->name('access.roles.update');
    Route::delete('/roles/{role}', [RolesController::class, 'destroy'])
        ->whereUuid('role')
        ->middleware('permission:roles.archive')
        ->name('access.roles.destroy');
    Route::patch('/roles/{role}/restore', [RolesController::class, 'restore'])
        ->whereUuid('role')
        ->middleware('permission:roles.restore')
        ->name('access.roles.restore');

    Route::middleware('permission:permissions.view|permissions.create|permissions.update|permissions.archive|permissions.restore')->group(function () {
        Route::get('/permissions/data', [PermissionController::class, 'data'])->name('access.permissions.data');
        Route::get('/permissions', [PermissionController::class, 'index'])->name('access.permissions.index');
    });

    Route::post('/permissions', [PermissionController::class, 'store'])
        ->middleware('permission:permissions.create')
        ->name('access.permissions.store');
    Route::patch('/permissions/{permission}', [PermissionController::class, 'update'])
        ->whereUuid('permission')
        ->middleware('permission:permissions.update')
        ->name('access.permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
        ->whereUuid('permission')
        ->middleware('permission:permissions.archive')
        ->name('access.permissions.destroy');
    Route::patch('/permissions/{permission}/restore', [PermissionController::class, 'restore'])
        ->whereUuid('permission')
        ->middleware('permission:permissions.restore')
        ->name('access.permissions.restore');
});
