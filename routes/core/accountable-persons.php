<?php

use App\Core\Http\Controllers\AccountablePersons\AccountablePersonActionController;
use App\Core\Http\Controllers\AccountablePersons\AccountablePersonController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])->group(function () {
    Route::get('/accountable-persons', [AccountablePersonController::class, 'index'])->name('accountable-persons.index');
    Route::get('/accountable-persons/data', [AccountablePersonController::class, 'data'])->name('accountable-persons.data');
    Route::get('/accountable-persons/suggest', [AccountablePersonController::class, 'suggest'])->name('accountable-persons.suggest');
    Route::post('/accountable-persons', [AccountablePersonActionController::class, 'store'])->name('accountable-persons.store');
    Route::put('/accountable-persons/{accountablePerson}', [AccountablePersonActionController::class, 'update'])
        ->whereUuid('accountablePerson')
        ->name('accountable-persons.update');
    Route::delete('/accountable-persons/{accountablePerson}', [AccountablePersonActionController::class, 'destroy'])
        ->whereUuid('accountablePerson')
        ->name('accountable-persons.destroy');
    Route::patch('/accountable-persons/{accountablePerson}/restore', [AccountablePersonActionController::class, 'restore'])
        ->whereUuid('accountablePerson')
        ->name('accountable-persons.restore');
});
