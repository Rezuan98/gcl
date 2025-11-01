<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\VerifyController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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

// routes/web.php
Route::get('/test-sms', function() {
    $smsService = new \App\Services\SmsService();
    
    // Check balance first
    $balance = $smsService->checkBalance();
    
    if ($balance !== null) {
        echo "✅ MiMSMS Connected! Balance: BDT {$balance}<br>";
        
        // Try sending test SMS
        $result = $smsService->sendOtp('01844909020', '123456');
        
        echo $result ? "✅ SMS Sent Successfully" : "❌ SMS Failed";
    } else {
        echo "❌ Connection Failed - Check credentials";
    }
});



/*
|--------------------------------------------------------------------------
| MiMSMS Connection Debugging Script
|--------------------------------------------------------------------------
| Add this route to routes/web.php to diagnose connection issues
|
| Usage: Visit /debug-sms in your browser
|--------------------------------------------------------------------------
*/




Route::get('/debug-sms', function() {
    $debug = [];
    
    // 1. Check Environment Variables
    $debug['step1_env_check'] = [
        'title' => '1️⃣ Environment Variables Check',
        'SMS_PROVIDER' => env('SMS_PROVIDER'),
        'MIMSMS_USERNAME' => env('MIMSMS_USERNAME') ? '✅ Set (' . env('MIMSMS_USERNAME') . ')' : '❌ Not Set',
        'MIMSMS_API_KEY' => env('MIMSMS_API_KEY') ? '✅ Set (' . substr(env('MIMSMS_API_KEY'), 0, 8) . '...)' : '❌ Not Set',
        'MIMSMS_SENDER_NAME' => env('MIMSMS_SENDER_NAME') ?? '❌ Not Set',
        'MIMSMS_TRANSACTION_TYPE' => env('MIMSMS_TRANSACTION_TYPE') ?? '❌ Not Set',
    ];
    
    // 2. Check Config Values
    $debug['step2_config_check'] = [
        'title' => '2️⃣ Configuration Values Check',
        'sms_provider' => config('services.sms.provider'),
        'mimsms_username' => config('services.mimsms.username') ? '✅ Set' : '❌ Not Set',
        'mimsms_api_key' => config('services.mimsms.api_key') ? '✅ Set' : '❌ Not Set',
        'mimsms_sender_name' => config('services.mimsms.sender_name'),
        'mimsms_transaction_type' => config('services.mimsms.transaction_type'),
    ];
    
    // 3. Test Direct API Call
    $debug['step3_direct_api_test'] = [
        'title' => '3️⃣ Direct MiMSMS API Test',
    ];
    
    try {
        $username = env('MIMSMS_USERNAME');
        $apiKey = env('MIMSMS_API_KEY');
        
        if (!$username || !$apiKey) {
            $debug['step3_direct_api_test']['error'] = '❌ Missing credentials in .env file';
        } else {
            // Test balance check API
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api.mimsms.com/api/SmsSending/balanceCheck', [
                    'UserName' => $username,
                    'Apikey' => $apiKey,
                ]);
            
            $debug['step3_direct_api_test']['http_status'] = $response->status();
            $debug['step3_direct_api_test']['response_body'] = $response->body();
            $debug['step3_direct_api_test']['response_json'] = $response->json();
            
            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['statusCode']) && $result['statusCode'] === '200') {
                    $debug['step3_direct_api_test']['status'] = '✅ API Connection Successful!';
                    $debug['step3_direct_api_test']['balance'] = 'BDT ' . ($result['responseResult'] ?? 'N/A');
                } else {
                    $debug['step3_direct_api_test']['status'] = '❌ API Returned Error';
                    $debug['step3_direct_api_test']['error_message'] = $result['responseResult'] ?? 'Unknown error';
                }
            } else {
                $debug['step3_direct_api_test']['status'] = '❌ HTTP Request Failed';
            }
        }
    } catch (\Exception $e) {
        $debug['step3_direct_api_test']['status'] = '❌ Exception Occurred';
        $debug['step3_direct_api_test']['error'] = $e->getMessage();
        $debug['step3_direct_api_test']['trace'] = $e->getTraceAsString();
    }
    
    // 4. Test SmsService Class
    $debug['step4_service_test'] = [
        'title' => '4️⃣ SmsService Class Test',
    ];
    
    try {
        if (class_exists(\App\Services\SmsService::class)) {
            $debug['step4_service_test']['class_exists'] = '✅ SmsService class found';
            
            $smsService = new \App\Services\SmsService();
            $debug['step4_service_test']['instance_created'] = '✅ Instance created successfully';
            
            // Test balance check
            try {
                $balance = $smsService->checkBalance();
                if ($balance !== null) {
                    $debug['step4_service_test']['balance_check'] = '✅ Success - BDT ' . $balance;
                } else {
                    $debug['step4_service_test']['balance_check'] = '❌ Returned null - Check logs';
                }
            } catch (\Exception $e) {
                $debug['step4_service_test']['balance_check'] = '❌ Error: ' . $e->getMessage();
            }
        } else {
            $debug['step4_service_test']['class_exists'] = '❌ SmsService class not found';
            $debug['step4_service_test']['expected_path'] = 'app/Services/SmsService.php';
        }
    } catch (\Exception $e) {
        $debug['step4_service_test']['error'] = $e->getMessage();
    }
    
    // 5. Network Connectivity Test
    $debug['step5_network_test'] = [
        'title' => '5️⃣ Network Connectivity Test',
    ];
    
    try {
        $response = Http::timeout(10)->get('https://api.mimsms.com');
        $debug['step5_network_test']['mimsms_reachable'] = $response->successful() ? '✅ MiMSMS API is reachable' : '❌ Cannot reach MiMSMS API';
        $debug['step5_network_test']['status_code'] = $response->status();
    } catch (\Exception $e) {
        $debug['step5_network_test']['mimsms_reachable'] = '❌ Network error: ' . $e->getMessage();
    }
    
    // 6. Common Issues & Solutions
    $debug['step6_solutions'] = [
        'title' => '6️⃣ Common Issues & Solutions',
        'issues' => [
            '❌ Missing credentials' => 'Solution: Add MIMSMS_USERNAME and MIMSMS_API_KEY to .env',
            '❌ Config cache' => 'Solution: Run "php artisan config:clear"',
            '❌ Invalid credentials' => 'Solution: Verify at https://www.mimsms.com/ > Developer Option',
            '❌ IP not whitelisted' => 'Solution: Add your server IP in MiMSMS portal',
            '❌ Network blocked' => 'Solution: Check firewall/security settings',
            '❌ Service class missing' => 'Solution: Ensure SmsService.php is in app/Services/',
        ],
    ];
    
    // Generate HTML Output
    return view('debug-sms', ['debug' => $debug]);
});