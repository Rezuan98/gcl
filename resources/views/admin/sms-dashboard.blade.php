<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Testing Dashboard | GCL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-box { 
            transition: all 0.3s ease; 
        }
        .status-box:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
        }
        .loading {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #10b981;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: inline-block;
            margin-left: 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-green-100 min-h-screen p-8">
    
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-emerald-800">📱 SMS Testing Dashboard</h1>
                    <p class="text-emerald-600 mt-2">Test your MiMSMS OTP integration</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Provider</div>
                    <div id="provider-badge" class="text-lg font-bold text-emerald-600">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Configuration Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Configuration Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 status-box">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Configuration</h3>
                    <div id="config-status" class="text-2xl">⏳</div>
                </div>
                <button onclick="checkConfig()" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium transition-colors">
                    Check Config
                </button>
                <div id="config-details" class="mt-4 text-sm text-gray-600 hidden"></div>
            </div>

            <!-- Balance Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 status-box">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">SMS Balance</h3>
                    <div id="balance-status" class="text-2xl">⏳</div>
                </div>
                <button onclick="checkBalance()" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-medium transition-colors">
                    Check Balance
                </button>
                <div id="balance-details" class="mt-4 text-sm text-gray-600 hidden"></div>
            </div>

            <!-- Send OTP Card -->
            <div class="bg-white rounded-xl shadow-lg p-6 status-box">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Send Test OTP</h3>
                    <div id="send-status" class="text-2xl">⏳</div>
                </div>
                <input id="phone-input" type="tel" placeholder="01XXXXXXXXX" 
                    value="01844909020"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3 focus:ring-2 focus:ring-emerald-500">
                <button onclick="sendTestOTP()" 
                    class="w-full bg-orange-600 hover:bg-orange-700 text-white py-2 rounded-lg font-medium transition-colors">
                    Send OTP
                </button>
                <div id="send-details" class="mt-4 text-sm text-gray-600 hidden"></div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📊 Test Results</h2>
            <div id="results" class="space-y-3">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-gray-600 text-center">
                    Click any button above to start testing...
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="mt-8 bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">🔗 Quick Links</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="/" class="text-center bg-emerald-50 hover:bg-emerald-100 text-emerald-700 py-3 rounded-lg font-medium transition-colors">
                    Dashboard
                </a>
                <a href="/verify" class="text-center bg-blue-50 hover:bg-blue-100 text-blue-700 py-3 rounded-lg font-medium transition-colors">
                    Verify Page
                </a>
                <a href="https://www.mimsms.com/" target="_blank" class="text-center bg-orange-50 hover:bg-orange-100 text-orange-700 py-3 rounded-lg font-medium transition-colors">
                    MiMSMS Portal
                </a>
                <a href="/admin/sms-dashboard" class="text-center bg-purple-50 hover:bg-purple-100 text-purple-700 py-3 rounded-lg font-medium transition-colors">
                    Reload Page
                </a>
            </div>
        </div>

        <!-- Documentation -->
        <div class="mt-8 bg-gradient-to-r from-emerald-50 to-green-50 border-l-4 border-emerald-600 rounded-lg p-6">
            <h3 class="text-lg font-bold text-emerald-800 mb-2">📖 Quick Tips</h3>
            <ul class="space-y-2 text-sm text-emerald-700">
                <li>✅ <strong>Testing Mode:</strong> Set <code>SMS_PROVIDER=log</code> in .env to test without sending real SMS</li>
                <li>✅ <strong>Production Mode:</strong> Set <code>SMS_PROVIDER=mimsms</code> to send real SMS</li>
                <li>✅ <strong>Phone Format:</strong> Use Bangladesh format: 01XXXXXXXXX or +8801XXXXXXXXX</li>
                <li>✅ <strong>Balance Alert:</strong> Recharge when balance is below BDT 100</li>
                <li>✅ <strong>IP Whitelist:</strong> Add your server IP in MiMSMS portal before going live</li>
            </ul>
        </div>
    </div>

    <script>
        // API endpoints
        const API = {
            config: '/admin/test-sms-config',
            balance: '/admin/test-sms-balance',
            send: '/admin/test-send-otp'
        };

        // Add result to display
        function addResult(type, message, data = null) {
            const resultsDiv = document.getElementById('results');
            const timestamp = new Date().toLocaleTimeString();
            
            const colorClasses = {
                success: 'bg-green-50 border-green-200 text-green-800',
                error: 'bg-red-50 border-red-200 text-red-800',
                info: 'bg-blue-50 border-blue-200 text-blue-800'
            };
            
            const icon = {
                success: '✅',
                error: '❌',
                info: 'ℹ️'
            };
            
            const resultHTML = `
                <div class="${colorClasses[type]} border rounded-lg p-4 animate-fade-in">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">${icon[type]}</span>
                                <span class="font-semibold">${message}</span>
                                <span class="text-xs opacity-60">${timestamp}</span>
                            </div>
                            ${data ? `<pre class="mt-2 text-xs bg-white bg-opacity-50 p-2 rounded overflow-x-auto">${JSON.stringify(data, null, 2)}</pre>` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            resultsDiv.innerHTML = resultHTML + resultsDiv.innerHTML;
        }

        // Check configuration
        async function checkConfig() {
            const statusEl = document.getElementById('config-status');
            const detailsEl = document.getElementById('config-details');
            
            statusEl.innerHTML = '<div class="loading"></div>';
            detailsEl.classList.add('hidden');
            
            try {
                const response = await fetch(API.config);
                const data = await response.json();
                
                if (data.success) {
                    statusEl.textContent = data.config.mimsms_configured ? '✅' : '❌';
                    
                    detailsEl.innerHTML = `
                        <div class="space-y-1">
                            <div><strong>Provider:</strong> ${data.config.provider}</div>
                            <div><strong>Username:</strong> ${data.config.mimsms_username}</div>
                            <div><strong>API Key:</strong> ${data.config.mimsms_api_key}</div>
                            <div><strong>Sender:</strong> ${data.config.mimsms_sender_name}</div>
                            <div><strong>Type:</strong> ${data.config.mimsms_transaction_type}</div>
                        </div>
                    `;
                    detailsEl.classList.remove('hidden');
                    
                    document.getElementById('provider-badge').textContent = data.config.provider.toUpperCase();
                    
                    addResult(data.config.mimsms_configured ? 'success' : 'error', 
                        `Configuration ${data.status}`, data.config);
                } else {
                    throw new Error('Configuration check failed');
                }
            } catch (error) {
                statusEl.textContent = '❌';
                addResult('error', 'Failed to check configuration', { error: error.message });
            }
        }

        // Check balance
        async function checkBalance() {
            const statusEl = document.getElementById('balance-status');
            const detailsEl = document.getElementById('balance-details');
            
            statusEl.innerHTML = '<div class="loading"></div>';
            detailsEl.classList.add('hidden');
            
            try {
                const response = await fetch(API.balance);
                const data = await response.json();
                
                if (data.success) {
                    statusEl.textContent = '✅';
                    detailsEl.innerHTML = `
                        <div class="text-center">
                            <div class="text-2xl font-bold text-emerald-600">BDT ${data.balance}</div>
                            ${data.balance < 100 ? '<div class="text-red-600 text-xs mt-1">⚠️ Low balance!</div>' : ''}
                        </div>
                    `;
                    detailsEl.classList.remove('hidden');
                    
                    addResult('success', `Balance: BDT ${data.balance}`, data);
                } else {
                    statusEl.textContent = '❌';
                    addResult('error', 'Failed to check balance', data);
                }
            } catch (error) {
                statusEl.textContent = '❌';
                addResult('error', 'Balance check error', { error: error.message });
            }
        }

        // Send test OTP
        async function sendTestOTP() {
            const statusEl = document.getElementById('send-status');
            const detailsEl = document.getElementById('send-details');
            const phoneInput = document.getElementById('phone-input');
            const phone = phoneInput.value.trim();
            
            if (!phone) {
                addResult('error', 'Please enter a phone number');
                return;
            }
            
            statusEl.innerHTML = '<div class="loading"></div>';
            detailsEl.classList.add('hidden');
            
            try {
                const response = await fetch(`${API.send}?phone=${encodeURIComponent(phone)}`);
                const data = await response.json();
                
                if (data.success) {
                    statusEl.textContent = '✅';
                    detailsEl.innerHTML = `
                        <div class="text-center">
                            <div class="text-green-600 font-bold">SMS Sent!</div>
                            <div class="text-xs mt-1">To: ${data.phone}</div>
                            ${data.otp !== '******' ? `<div class="text-xs mt-1 font-mono bg-yellow-100 p-2 rounded">OTP: ${data.otp}</div>` : ''}
                            <div class="text-xs mt-1 text-gray-500">Provider: ${data.provider}</div>
                        </div>
                    `;
                    detailsEl.classList.remove('hidden');
                    
                    addResult('success', `OTP sent to ${data.phone}`, data);
                } else {
                    statusEl.textContent = '❌';
                    addResult('error', `Failed to send OTP to ${phone}`, data);
                }
            } catch (error) {
                statusEl.textContent = '❌';
                addResult('error', 'Send OTP error', { error: error.message });
            }
        }

        // Auto-check config on load
        window.addEventListener('load', () => {
            checkConfig();
        });
    </script>
</body>
</html>