<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiMSMS Connection Debug | GCL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .debug-box { transition: all 0.3s ease; }
        .debug-box:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen p-8">
    
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h1 class="text-3xl font-bold text-gray-800">🔍 MiMSMS Connection Debugger</h1>
            <p class="text-gray-600 mt-2">Diagnose connection issues with MiMSMS API</p>
            <div class="mt-4 flex gap-3">
                <button onclick="location.reload()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Refresh Test
                </button>
                <a href="/admin/sms-dashboard" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                    SMS Dashboard
                </a>
                <a href="/" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Dashboard
                </a>
            </div>
        </div>

        @foreach($debug as $key => $section)
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6 debug-box">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">
                    {{ $section['title'] ?? 'Debug Section' }}
                </h2>
                
                <div class="space-y-3">
                    @foreach($section as $itemKey => $itemValue)
                        @if($itemKey !== 'title')
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="font-semibold text-gray-700 min-w-[200px]">
                                        {{ ucwords(str_replace('_', ' ', $itemKey)) }}:
                                    </div>
                                    <div class="flex-1 font-mono text-sm">
                                        @if(is_array($itemValue))
                                            <pre class="bg-white p-3 rounded border border-gray-200 overflow-x-auto">{{ json_encode($itemValue, JSON_PRETTY_PRINT) }}</pre>
                                        @elseif(is_bool($itemValue))
                                            <span class="{{ $itemValue ? 'text-green-600' : 'text-red-600' }} font-bold">
                                                {{ $itemValue ? '✅ True' : '❌ False' }}
                                            </span>
                                        @elseif(str_contains($itemValue, '✅'))
                                            <span class="text-green-600 font-bold">{{ $itemValue }}</span>
                                        @elseif(str_contains($itemValue, '❌'))
                                            <span class="text-red-600 font-bold">{{ $itemValue }}</span>
                                        @else
                                            <span class="text-gray-800">{{ $itemValue ?? 'null' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Quick Actions -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-600 rounded-lg p-6">
            <h3 class="text-lg font-bold text-blue-800 mb-3">🛠️ Quick Fixes</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-start gap-2">
                    <span class="text-blue-600">1️⃣</span>
                    <div>
                        <strong>If credentials are missing:</strong>
                        <pre class="bg-white p-2 rounded mt-1 text-xs overflow-x-auto">
# Add to .env
SMS_PROVIDER=log
MIMSMS_USERNAME=rezuanahmmeds@gmail.com
MIMSMS_API_KEY=CFI8M8OH66KMAS1
MIMSMS_SENDER_NAME=GCL
MIMSMS_TRANSACTION_TYPE=T</pre>
                    </div>
                </div>
                
                <div class="flex items-start gap-2">
                    <span class="text-blue-600">2️⃣</span>
                    <div>
                        <strong>Clear cache:</strong>
                        <pre class="bg-white p-2 rounded mt-1 text-xs">php artisan config:clear && php artisan cache:clear</pre>
                    </div>
                </div>
                
                <div class="flex items-start gap-2">
                    <span class="text-blue-600">3️⃣</span>
                    <div>
                        <strong>Verify config/services.php has:</strong>
                        <pre class="bg-white p-2 rounded mt-1 text-xs overflow-x-auto">
'sms' => [
    'provider' => env('SMS_PROVIDER', 'log'),
],
'mimsms' => [
    'username' => env('MIMSMS_USERNAME'),
    'api_key' => env('MIMSMS_API_KEY'),
    'sender_name' => env('MIMSMS_SENDER_NAME', 'GCL'),
    'transaction_type' => env('MIMSMS_TRANSACTION_TYPE', 'T'),
],</pre>
                    </div>
                </div>
                
                <div class="flex items-start gap-2">
                    <span class="text-blue-600">4️⃣</span>
                    <div>
                        <strong>Check SmsService.php exists:</strong>
                        <pre class="bg-white p-2 rounded mt-1 text-xs">app/Services/SmsService.php</pre>
                    </div>
                </div>

                <div class="flex items-start gap-2">
                    <span class="text-blue-600">5️⃣</span>
                    <div>
                        <strong>Whitelist your IP at MiMSMS portal:</strong>
                        <pre class="bg-white p-2 rounded mt-1 text-xs">https://www.mimsms.com/ > Utility > Developer Option > Whitelisted IP</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>🔄 Last checked: {{ now()->format('M j, Y \a\t g:i:s A') }}</p>
            <p class="mt-2">
                Need help? Check 
                <a href="https://www.mimsms.com/api-documentation/" target="_blank" class="text-blue-600 hover:underline">MiMSMS API Documentation</a>
            </p>
        </div>
    </div>

</body>
</html>