<?php

use App\Core\Http\Controllers\Auth\AuthController;
use App\Core\Http\Controllers\Auth\ForgotPasswordController;
use App\Core\Http\Controllers\Auth\ResetPasswordController;
use App\Core\Http\Controllers\Modules\ModuleSelectorController;
use Illuminate\Support\Facades\Route;

Route::get('/wc-login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/wc-login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');
Route::redirect('/login', '/wc-login', 301);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:password-reset-link')
        ->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:password-reset')
        ->name('password.update');
});

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/capture-location', [AuthController::class, 'captureLocation']);

    Route::get('/modules', [ModuleSelectorController::class, 'index'])->name('modules.index');
    Route::get('/modules/{moduleCode}', [ModuleSelectorController::class, 'open'])->name('modules.open');
});
