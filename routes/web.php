<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

// Root URL will redirect to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard route, protected by auth
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Guest middleware will redirect authenticated users away from login
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');