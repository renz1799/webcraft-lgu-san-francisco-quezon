<?php

use App\Core\Http\Controllers\Auth\AuthController;
use App\Core\Http\Controllers\Modules\ModuleSelectorController;
use Illuminate\Support\Facades\Route;

Route::get('/wc-login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/wc-login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');
Route::redirect('/login', '/wc-login', 301);
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/capture-location', [AuthController::class, 'captureLocation']);

    Route::get('/modules', [ModuleSelectorController::class, 'index'])->name('modules.index');
    Route::get('/modules/{moduleCode}', [ModuleSelectorController::class, 'open'])->name('modules.open');
});
