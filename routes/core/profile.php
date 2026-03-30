<?php

use App\Core\Http\Controllers\Profile\UserProfileController;
use App\Core\Http\Controllers\Settings\ThemeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::get('/mail-settings', function (Request $request) {
        return redirect()->route('profile.index', $request->query());
    });
    Route::put('/mail-settings', [UserProfileController::class, 'update']);

    Route::middleware('active_module')->group(function () {
        Route::post('/theme/style', [ThemeController::class, 'updateStyle'])
            ->name('theme.style.update');
        Route::post('/theme/colors', [ThemeController::class, 'updateColors'])
            ->middleware('permission:theme.update_colors')
            ->name('theme.colors.update');
    });
});
