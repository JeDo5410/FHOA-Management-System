<?php

use App\Http\Controllers\AccountPayableController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountReceivableController;
use App\Http\Controllers\ReportExtractionController;
use App\Http\Controllers\StatementOfAccountController;
use Illuminate\Support\Facades\Auth;

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
        Route::get('/search-by-name', [ResidentController::class, 'searchByName'])->name('residents.search_by_name');
        Route::get('/get-member-details/{mem_id}', [ResidentController::class, 'getMemberDetails'])->name('residents.details');
        Route::get('/validate-address/{addressId}', [ResidentController::class, 'validateAddress'])->name('residents.validate-address');
        Route::post('/', [ResidentController::class, 'store'])->name('residents.store');
    });

    // Account Payables routes
    Route::prefix('accounts')->group(function () {
        // Payables routes
        Route::get('/payables', [AccountPayableController::class, 'index'])->name('accounts.payables');
        Route::post('/payables/store', [AccountPayableController::class, 'store'])->name('accounts.payables.store');
        
        // Receivables routes
        Route::get('/receivables', [AccountReceivableController::class, 'index'])->name('accounts.receivables');
        Route::post('/receivables/store', [AccountReceivableController::class, 'store'])->name('accounts.receivables.store');
        Route::get('/receivables/payment-history/{memberId}', [AccountReceivableController::class, 'getPaymentHistory'])
            ->name('accounts.receivables.payment-history');
        Route::get('/receivables/check-invoice/{invoiceNumber}', [AccountReceivableController::class, 'checkInvoice'])
            ->name('accounts.receivables.check-invoice');
        
        Route::get('/soa', [StatementOfAccountController::class, 'index'])
            ->name('accounts.soa.index');
        Route::get('/soa/print/{id}', [StatementOfAccountController::class, 'printStatement'])
            ->name('accounts.soa.print');
        Route::get('/soa/print-multiple', [StatementOfAccountController::class, 'printMultiple'])
            ->name('accounts.soa.print-multiple');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/extraction', [ReportExtractionController::class, 'index'])
            ->name('reports.extraction');
        Route::get('/get-members-data', [ReportExtractionController::class, 'getMembersData'])
            ->name('reports.get-members-data');
        Route::get('/get-car-sticker-data', [ReportExtractionController::class, 'getCarStickerData'])
            ->name('reports.get-car-sticker-data');
        Route::get('/download/members-data', [ReportExtractionController::class, 'downloadMembersData'])
            ->name('reports.download.members-data');
        Route::get('/download/car-sticker', [ReportExtractionController::class, 'downloadCarStickerData'])
            ->name('reports.download.car-sticker');
        Route::get('/get-payable-data', [ReportExtractionController::class, 'getPayableData'])
            ->name('reports.get-payable-data');
        Route::get('/download/payable-data', [ReportExtractionController::class, 'downloadPayableData'])
            ->name('reports.download.payable-data');
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

    Route::get('/check-session-status', function () {
        if (Auth::check()) {
            return response()->json(['authenticated' => true]);
        }
        return response()->json(['authenticated' => false], 401);
    })->middleware('web');
    Route::get('/refresh-csrf', [App\Http\Controllers\Auth\LoginController::class, 'refreshToken'])->middleware('web');