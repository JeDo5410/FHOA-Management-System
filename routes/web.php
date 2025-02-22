<?php

use App\Http\Controllers\AccountPayableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountReceivableController;

// Root URL will redirect to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Guest middleware group (unchanged)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/check-username', [LoginController::class, 'checkUsername']);
    Route::post('/set-initial-password', [LoginController::class, 'setInitialPassword']);
});

// Admin-only routes
Route::middleware(['auth', 'role:1'])->group(function () {
    // Users management routes
    Route::get('/users', [UserController::class, 'users'])->name('users.users_management');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
});

// Routes accessible by all authenticated users (Admin, Editor, Viewer)
Route::middleware(['auth', 'role:1,2,3'])->group(function () {
    // Dashboard
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Residents routes
    Route::prefix('residents')->group(function () {
        Route::get('/', [ResidentController::class, 'residentsData'])->name('residents.residents_data');
        Route::get('/search-address', [ResidentController::class, 'searchAddress'])->name('residents.search');
        Route::get('/get-member-details/{mem_id}', [ResidentController::class, 'getMemberDetails'])->name('residents.details');
        Route::get('/validate-address/{addressId}', [ResidentController::class, 'validateAddress'])->name('residents.validate-address');
        Route::post('/', [ResidentController::class, 'store'])->name('residents.store');
    });

    // Account Payables routes
    Route::prefix('accounts')->group(function () {
        Route::get('/payables', [AccountPayableController::class, 'index'])->name('accounts.payables');
        Route::post('/payables/store', [AccountPayableController::class, 'store'])->name('accounts.payables.store');
        
        // New Account Receivables routes
        Route::get('/receivables', [AccountReceivableController::class, 'index'])->name('accounts.receivables');
        Route::post('/receivables/store', [AccountReceivableController::class, 'store'])->name('accounts.receivables.store');
    });
});

// Utility routes accessible to all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/refresh-csrf', function () {
        return response()->json(['token' => csrf_token()]);
    });

    Route::get('/check-session', function () {
        return response()->json(['status' => 'valid']);
    });
});

// Logout route (unchanged)
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->withoutMiddleware(['auth'])
    ->middleware(['web']);
