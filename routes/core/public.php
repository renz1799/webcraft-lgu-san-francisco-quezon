<?php

use App\Core\Http\Controllers\Dashboard\DashboardsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardsController::class, 'index'])->name('landing');
