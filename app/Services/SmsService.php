<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    /**
     * Send OTP message
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "Your GCL Tender Verification OTP: {$otp}. Valid for 5 minutes. Do not share this code.";
        
        // Choose your SMS provider
        $provider = config('services.sms.provider', 'log');

        return match($provider) {
            'mimsms' => $this->sendMimSms($phone, $message),
            'twilio' => $this->sendTwilioSms($phone, $message),
            'log' => $this->logSms($phone, $message),
            default => $this->logSms($phone, $message),
        };
    }

    /**
     * Send SMS using MiMSMS API (Bangladesh Local Gateway)
     */
    public function sendMimSms(string $phone, string $message): bool
    {
        try {
            $username = config('services.mimsms.username');
            $apiKey = config('services.mimsms.api_key');
            $senderName = config('services.mimsms.sender_name', 'GCL');
            $transactionType = config('services.mimsms.transaction_type', 'T'); // T = Transactional, P = Promotional

            // Validate configuration
            if (!$username || !$apiKey) {
                throw new \Exception('MiMSMS configuration missing. Please check your .env file.');
            }

            // Format phone number for Bangladesh (88018xxxxxxxx)
            $formattedPhone = $this->formatPhoneNumberForBangladesh($phone);

            // Prepare API request payload
            $payload = [
                'UserName' => $username,
                'Apikey' => $apiKey,
                'MobileNumber' => $formattedPhone,
                'CampaignId' => null, // Only required for promotional SMS
                'SenderName' => $senderName,
                'TransactionType' => $transactionType,
                'Message' => $message,
            ];

            // Send request to MiMSMS API
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api.mimsms.com/api/SmsSending/SMS', $payload);

            // Log the full response for debugging
            Log::info('MiMSMS API Response', [
                'status_code' => $response->status(),
                'body' => $response->body(),
                'phone' => $formattedPhone,
            ]);

            // Check if request was successful
            if ($response->successful()) {
                $result = $response->json();
                
                // Check if MiMSMS returned success status
                if (isset($result['statusCode']) && $result['statusCode'] === '200') {
                    Log::info("SMS sent successfully via MiMSMS", [
                        'phone' => $formattedPhone,
                        'trxnId' => $result['trxnId'] ?? 'N/A',
                        'status' => $result['status'] ?? 'N/A',
                    ]);
                    return true;
                }
            }

            // Log error if not successful
            Log::error("MiMSMS API returned error", [
                'status_code' => $response->status(),
                'response' => $response->body(),
                'phone' => $formattedPhone,
            ]);
            
            return false;

        } catch (\Exception $e) {
            Log::error("MiMSMS SMS sending failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'phone' => $phone,
            ]);
            return false;
        }
    }

    /**
     * Send SMS using Twilio (International)
     */
    // public function sendTwilioSms(string $phone, string $message): bool
    // {
    //     try {
    //         $accountSid = config('services.twilio.sid');
    //         $authToken = config('services.twilio.token');
    //         $fromNumber = config('services.twilio.from');

    //         if (!$accountSid || !$authToken || !$fromNumber) {
    //             throw new \Exception('Twilio configuration missing');
    //         }

    //         $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

    //         $response = Http::asForm()
    //             ->withBasicAuth($accountSid, $authToken)
    //             ->post($url, [
    //                 'From' => $fromNumber,
    //                 'To' => $this->formatPhoneNumberWithPlus($phone),
    //                 'Body' => $message,
    //             ]);

    //         if ($response->successful()) {
    //             Log::info("SMS sent successfully via Twilio to {$phone}");
    //             return true;
    //         } else {
    //             Log::error("Twilio SMS failed", ['response' => $response->body()]);
    //             return false;
    //         }

    //     } catch (\Exception $e) {
    //         Log::error("Twilio SMS sending failed: " . $e->getMessage());
    //         return false;
    //     }
    // }

    /**
     * Log SMS for development/testing (default mode)
     */
    private function logSms(string $phone, string $message): bool
    {
        Log::info("📱 SMS would be sent (LOG MODE)", [
            'phone' => $phone,
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        // Always return true in log mode for testing
        return true;
    }

    /**
     * Format phone number for Bangladesh (MiMSMS format: 88018xxxxxxxx)
     * Removes +, spaces, and ensures proper format
     */
    private function formatPhoneNumberForBangladesh(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Remove + sign
        $phone = str_replace('+', '', $phone);
        
        // If starts with 0, replace with 880
        if (str_starts_with($phone, '0')) {
            return '880' . substr($phone, 1);
        }
        
        // If starts with 880, keep as is
        if (str_starts_with($phone, '880')) {
            return $phone;
        }
        
        // If starts with 88, keep as is (might be 88017...)
        if (str_starts_with($phone, '88')) {
            return $phone;
        }
        
        // Otherwise, assume it's a local number without country code
        return '880' . $phone;
    }

    /**
     * Format phone number with + sign (for Twilio and other international gateways)
     */
    private function formatPhoneNumberWithPlus(string $phone): string
    {
        $formatted = $this->formatPhoneNumberForBangladesh($phone);
        return '+' . $formatted;
    }

    /**
     * Send SMS to multiple recipients (One-to-Many)
     * Useful for sending notifications to multiple users
     */
    public function sendBulkSms(array $phones, string $message): array
    {
        $results = [];
        
        foreach ($phones as $phone) {
            $results[$phone] = $this->sendOtp($phone, $message);
        }
        
        return $results;
    }

    /**
     * Check SMS balance (for MiMSMS)
     */
    public function checkBalance(): ?float
    {
        try {
            $username = config('services.mimsms.username');
            $apiKey = config('services.mimsms.api_key');

            if (!$username || !$apiKey) {
                throw new \Exception('MiMSMS configuration missing');
            }

            $response = Http::post('https://api.mimsms.com/api/SmsSending/balanceCheck', [
                'UserName' => $username,
                'Apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['statusCode']) && $result['statusCode'] === '200') {
                    return (float) $result['responseResult'];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error("Balance check failed: " . $e->getMessage());
            return null;
        }
    }
}