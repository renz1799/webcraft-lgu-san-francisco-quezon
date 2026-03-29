<?php

use App\Core\Http\Controllers\Access\PermissionController;
use App\Core\Http\Controllers\Access\RolesController;
use App\Core\Http\Controllers\Access\CoreUserOnboardingController;
use App\Core\Http\Controllers\Access\UserAccessController;
use App\Core\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])->group(function () {
    Route::middleware('role:Administrator|admin')->group(function () {
        Route::get('/sign-up', [AuthController::class, 'showSignUpForm'])->name('sign-up');
        Route::get('/register/options', [AuthController::class, 'registrationOptions'])->name('register.options');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
    });

    Route::middleware('role:Administrator|admin')->group(function () {
        Route::get('/users/data', [UserAccessController::class, 'data'])->name('access.users.data');
        Route::get('/users', [UserAccessController::class, 'index'])->name('access.users.index');
        Route::get('/users/create', [CoreUserOnboardingController::class, 'create'])->name('access.users.create');
        Route::post('/users', [CoreUserOnboardingController::class, 'store'])->name('access.users.store');

        // Backward-compatible aliases while callers migrate off the legacy URI.
        Route::get('/users/permissions/data', [UserAccessController::class, 'data'])->name('legacy.access.users.data');
        Route::get('/users/permissions', [UserAccessController::class, 'index'])->name('legacy.access.users.index');

        Route::prefix('users')
            ->whereUuid(['user'])
            ->group(function () {
                Route::get('{user}/permissions', [UserAccessController::class, 'show'])->name('access.users.show');
                Route::get('{user}/access-overview', [UserAccessController::class, 'accessOverview'])->name('access.users.access-overview');
                Route::get('{user}/permissions/edit', [UserAccessController::class, 'edit'])->name('access.users.edit');
                Route::patch('{user}/permissions', [UserAccessController::class, 'updateModulePermissions'])->name('access.users.update');
                Route::patch('{user}/status', [UserAccessController::class, 'updateStatus'])->name('access.users.status.update');
                Route::post('{user}/reset-password', [UserAccessController::class, 'resetPassword'])->name('access.users.password.reset');
                Route::delete('{user}', [UserAccessController::class, 'destroy'])->name('access.users.destroy');
                Route::patch('{user}/restore', [UserAccessController::class, 'restore'])->name('access.users.restore');
            });
    });

    Route::middleware('role:Administrator|admin')->group(function () {
        Route::resource('roles', RolesController::class)
            ->whereUuid(['role'])
            ->except(['show']);

        Route::get('/roles/data', [RolesController::class, 'data'])->name('access.roles.data');
        Route::get('/roles', [RolesController::class, 'index'])->name('access.roles.index');
        Route::get('/roles/create', [RolesController::class, 'create'])->name('access.roles.create');
        Route::post('/roles', [RolesController::class, 'store'])->name('access.roles.store');
        Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])->whereUuid('role')->name('access.roles.edit');
        Route::match(['put', 'patch'], '/roles/{role}', [RolesController::class, 'update'])->whereUuid('role')->name('access.roles.update');
        Route::delete('/roles/{role}', [RolesController::class, 'destroy'])->whereUuid('role')->name('access.roles.destroy');
        Route::patch('/roles/{role}/restore', [RolesController::class, 'restore'])->whereUuid('role')->name('access.roles.restore');
    });

    Route::middleware('role:Administrator|admin')->group(function () {
        Route::get('/permissions/data', [PermissionController::class, 'data'])->name('access.permissions.data');
        Route::get('/permissions', [PermissionController::class, 'index'])->name('access.permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('access.permissions.store');
        Route::patch('/permissions/{permission}', [PermissionController::class, 'update'])
            ->whereUuid('permission')
            ->name('access.permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
            ->whereUuid('permission')
            ->name('access.permissions.destroy');
        Route::patch('/permissions/{permission}/restore', [PermissionController::class, 'restore'])
            ->whereUuid('permission')
            ->name('access.permissions.restore');
    });
});
