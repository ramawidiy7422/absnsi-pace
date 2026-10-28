<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/lupa-password', [AuthController::class, 'showLupaPassword'])->name('lupa-password');
    Route::post('/lupa-password', [AuthController::class, 'lupaPassword'])->middleware('throttle:5,1')->name('lupa-password.submit');
    Route::get('/lupa-password/sukses', [AuthController::class, 'lupaPasswordSukses'])->name('lupa-password.sukses');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
