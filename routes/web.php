<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;

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
    Route::post('/check-username', [LoginController::class, 'checkUsername']);
    Route::post('/set-initial-password', [LoginController::class, 'setInitialPassword']);

});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.users_management');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
});

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

// In web.php
Route::get('/check-session', function () {
    return response()->json(['status' => 'valid']);
});

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->withoutMiddleware(['auth'])  // Allow the route to process without authentication
    ->middleware(['web']);  // Keep the web middleware for session handling