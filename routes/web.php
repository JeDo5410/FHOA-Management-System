<?php

use App\Http\Controllers\AccountPayableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;

// Root URL will redirect to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard route, protected by auth
Route::get('/', function () {
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
    // Users routes
    Route::get('/users', [UserController::class, 'users'])->name('users.users_management');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    
    // Residents routes
    Route::prefix('residents')->group(function () {
        Route::get('/', [ResidentController::class, 'residentsData'])->name('residents.residents_data');
        Route::get('/search-address', [ResidentController::class, 'searchAddress'])->name('residents.search');
        Route::get('/get-member-details/{mem_id}', [ResidentController::class, 'getMemberDetails'])->name('residents.details');
        Route::post('/', [ResidentController::class, 'store'])->name('residents.store');
    });

    // Account Payables routes
    Route::prefix('accounts')->group(function () {
    Route::get('/payables', [AccountPayableController::class, 'index'])->name('accounts.payables');
    Route::post('/payables/store', [AccountPayableController::class, 'store'])->name('accounts.payables.store');
    });
}); 

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
});

Route::get('/check-session', function () {
    return response()->json(['status' => 'valid']);
});

// Logout route
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->withoutMiddleware(['auth'])  // Allow the route to process without authentication
    ->middleware(['web']);  // Keep the web middleware for session handling