<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\VerifyController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard Routes
Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');

// Public Verification Routes
Route::get('/verify', [DashboardController::class, 'show'])->name('verify.show');
Route::post('/verify/send-otp', [VerifyController::class, 'sendOtp'])->name('verify.sendOtp');
Route::post('/verify/check-otp', [VerifyController::class, 'checkOtp'])->name('verify.checkOtp');

// QR Code Routes
Route::get('/qr/standard/download', [QrController::class, 'standard'])->name('qr.standard.download');
Route::get('/qr/standard/inline', [QrController::class, 'standardInline'])->name('qr.standard.inline');

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
});

// API Routes for AJAX calls (optional)
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/proposals/stats', [ProposalController::class, 'getDashboardStats'])->name('proposals.stats');
});

// Add these to your routes/web.php


Route::prefix('api/verify')->group(function () {
    Route::post('/lookup', [VerifyController::class, 'lookup'])->name('verify.lookup');
    Route::post('/send-otp', [VerifyController::class, 'sendOtp'])->name('verify.send-otp');
    Route::post('/verify-otp', [VerifyController::class, 'verifyOtp'])->name('verify.verify-otp');
});

Route::get('/verify', [VerifyController::class, 'show'])->name('verify.show');