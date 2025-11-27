<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\VerifyController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Public Verification Page and API Routes
Route::get('/verify/{token}', [VerifyController::class, 'show'])->name('proposal.verify');

Route::prefix('api/verify')->group(function () {
    Route::post('/send-otp', [VerifyController::class, 'sendOtp'])->name('verify.send-otp');
    Route::post('/verify-otp', [VerifyController::class, 'verifyOtp'])->name('verify.verify-otp');
});

// PDF Download (only after verification)
Route::get('/proposal/{token}/download', [VerifyController::class, 'downloadPdf'])->name('proposal.download');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Require Login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

    // Proposal Routes
    Route::prefix('proposals')->name('proposals.')->group(function () {
        // List routes
        Route::get('/', [ProposalController::class, 'index'])->name('index');
        Route::get('/drafts', [ProposalController::class, 'drafts'])->name('drafts');
        
        // CRUD routes
        Route::post('/', [ProposalController::class, 'store'])->name('store');
        Route::get('/{proposal}', [ProposalController::class, 'show'])->name('show');
        Route::patch('/{proposal}/status', [ProposalController::class, 'updateStatus'])->name('updateStatus');
        Route::patch('/{proposal}/publish', [ProposalController::class, 'publish'])->name('publish');
        Route::delete('/{proposal}', [ProposalController::class, 'destroy'])->name('destroy');
        
        // Copy URL
        Route::get('/{proposal}/copy-url', [ProposalController::class, 'copyUrl'])->name('copyUrl');
    });

    // API Routes for AJAX calls
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/proposals/stats', [ProposalController::class, 'getDashboardStats'])->name('proposals.stats');
    });
});

/*
|--------------------------------------------------------------------------
| Breeze Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';