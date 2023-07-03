<?php
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::middleware('guest')->group(function () { 
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.request');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
});

Route::middleware('auth')->group(function () { 
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
