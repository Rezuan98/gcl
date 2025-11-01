<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    private const MIMSMS_API_URL = 'https://smsplus.sslwireless.com/api/v3/send-sms';
    
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
            $apiToken = config('services.mimsms.api_key');
            $sid = config('services.mimsms.sender_name', 'GCL');

            if (!$apiToken) {
                Log::error('MiMSMS API Key is missing');
                throw new \Exception('MiMSMS API Key is missing.');
            }

            $formattedPhone = $this->formatPhoneNumberForBangladesh($phone);

            Log::info('Sending SMS via MiMSMS', [
                'phone' => $formattedPhone,
                'sender' => $sid
            ]);

            $payload = [
                'api_token' => $apiToken,
                'sid' => $sid,
                'sms' => $message,
                'msisdn' => $formattedPhone,
                'csms_id' => uniqid('gcl_', true),
            ];

            $response = Http::withOptions([
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_TIMEOUT => 30,
            ])->asForm()->post(self::MIMSMS_API_URL, $payload);

            Log::info('MiMSMS Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (
                    (isset($result['status']) && strtoupper($result['status']) === 'SUCCESS') ||
                    (isset($result['status_code']) && in_array($result['status_code'], [200, '200']))
                ) {
                    Log::info("✅ SMS sent successfully", ['phone' => $formattedPhone]);
                    return true;
                }
            }

            Log::error("SMS sending failed", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return false;

        } catch (\Exception $e) {
            Log::error("SMS Error: " . $e->getMessage());
            return false;
        }
    }

    private function logSms(string $phone, string $message): bool
    {
        Log::info("SMS LOG MODE", [
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
                'api_key_set' => !empty(config('services.mimsms.api_key')),
                'provider' => config('services.sms.provider'),
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
            $apiToken = config('services.mimsms.api_key');
            if (!$apiToken) {
                return ['success' => false, 'message' => 'API key missing'];
            }

            $response = Http::withOptions([
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ])->timeout(10)->asForm()->post(
                'https://smsplus.sslwireless.com/api/v3/check-balance',
                ['api_token' => $apiToken]
            );

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'balance' => $result['balance'] ?? $result['current_balance'] ?? 0,
                ];
            }

            return ['success' => false, 'response' => $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}