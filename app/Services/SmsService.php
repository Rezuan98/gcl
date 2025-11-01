<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    // CORRECT API ENDPOINT
    private const MIMSMS_API_URL = 'https://api.mimsms.com/api/SmsSending/SMS';
    
    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "Your GCL Tender Verification OTP: {$otp}. Valid for 5 minutes. Do not share this code.";
        $provider = config('services.sms.provider', 'log');

        return match($provider) {
            'mimsms' => $this->sendMimSms($phone, $message),
            'log' => $this->logSms($phone, $message),
            default => $this->logSms($phone, $message),
        };
    }

    public function sendMimSms(string $phone, string $message): bool
    {
        try {
            // Get credentials - note: we need USERNAME not just API key
            $username = config('services.mimsms.username');
            $apiKey = config('services.mimsms.api_key');
            $senderName = config('services.mimsms.sender_name', '8809601004835');

            if (!$username || !$apiKey) {
                Log::error('MiMSMS credentials missing');
                throw new \Exception('MiMSMS credentials missing.');
            }

            $formattedPhone = $this->formatPhoneNumberForBangladesh($phone);

            Log::info('🚀 Sending SMS via MiMSMS (CORRECT API)', [
                'phone' => $formattedPhone,
                'sender' => $senderName
            ]);

            // CORRECT PAYLOAD FORMAT (JSON)
            $payload = [
                'UserName' => $username,
                'Apikey' => $apiKey,
                'SenderName' => $senderName,
                'Message' => $message,
                'MobileNumber' => $formattedPhone,
                'TransactionType' => 'T', // T = Transactional (for OTP)
            ];

            // Send as JSON (not form data!)
            $response = Http::withOptions([
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Force IPv4
                CURLOPT_TIMEOUT => 30,
            ])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post(self::MIMSMS_API_URL, $payload);

            Log::info('📡 MiMSMS Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                // Check for success (statusCode: "200" and status: "Success")
                if (
                    (isset($result['statusCode']) && $result['statusCode'] === '200') &&
                    (isset($result['status']) && strtolower($result['status']) === 'success')
                ) {
                    Log::info("✅ SMS SENT SUCCESSFULLY!", [
                        'phone' => $formattedPhone,
                        'trxnId' => $result['trxnId'] ?? 'N/A'
                    ]);
                    return true;
                }
            }

            Log::error("❌ SMS sending failed", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return false;

        } catch (\Exception $e) {
            Log::error("❌ SMS Error: " . $e->getMessage());
            return false;
        }
    }

    private function logSms(string $phone, string $message): bool
    {
        Log::info("📱 SMS LOG MODE", [
            'phone' => $phone,
            'message' => $message
        ]);
        return true;
    }

    private function formatPhoneNumberForBangladesh(string $phone): string
    {
        $phone = preg_replace('/[^\d]/', '', $phone);
        $phone = ltrim($phone, '0');
        
        if (str_starts_with($phone, '880')) {
            return $phone;
        }
        
        if (str_starts_with($phone, '88') && strlen($phone) === 13) {
            return $phone;
        }
        
        if (str_starts_with($phone, '1') && strlen($phone) === 10) {
            return '880' . $phone;
        }
        
        return '880' . $phone;
    }

    public function testConnection(): array
    {
        return [
            'config' => [
                'username_set' => !empty(config('services.mimsms.username')),
                'api_key_set' => !empty(config('services.mimsms.api_key')),
                'provider' => config('services.sms.provider'),
                'api_url' => self::MIMSMS_API_URL,
            ],
            'server_ip' => $this->getServerIp(),
        ];
    }

    private function getServerIp(): ?string
    {
        try {
            $response = Http::timeout(5)->get('https://api.ipify.org?format=json');
            return $response->json()['ip'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function checkBalance(): ?array
    {
        try {
            $username = config('services.mimsms.username');
            $apiKey = config('services.mimsms.api_key');
            
            if (!$username || !$apiKey) {
                return ['success' => false, 'message' => 'Credentials missing'];
            }

            $response = Http::withOptions([
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ])
            ->timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.mimsms.com/api/SmsSending/balanceCheck', [
                'UserName' => $username,
                'Apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'balance' => $result['responseResult'] ?? 0,
                    'data' => $result
                ];
            }

            return ['success' => false, 'response' => $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}