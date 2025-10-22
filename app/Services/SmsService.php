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
            'twilio' => $this->sendTwilioSms($phone, $message),
            'local' => $this->sendLocalSms($phone, $message),
            'log' => $this->logSms($phone, $message),
            default => $this->logSms($phone, $message),
        };
    }

    /**
     * Send SMS using Twilio
     */
    public function sendTwilioSms(string $phone, string $message): bool
    {
        try {
            $accountSid = config('services.twilio.sid');
            $authToken = config('services.twilio.token');
            $fromNumber = config('services.twilio.from');

            if (!$accountSid || !$authToken || !$fromNumber) {
                throw new \Exception('Twilio configuration missing');
            }

            $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

            $response = Http::asForm()
                ->withBasicAuth($accountSid, $authToken)
                ->post($url, [
                    'From' => $fromNumber,
                    'To' => $this->formatPhoneNumber($phone),
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$phone}");
                return true;
            } else {
                Log::error("Twilio SMS failed", ['response' => $response->body()]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("SMS sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS using local Bangladesh SMS gateway
     */
    public function sendLocalSms(string $phone, string $message): bool
    {
        try {
            // Example for a local SMS gateway API
            $apiKey = config('services.sms.api_key');
            $senderId = config('services.sms.sender_id', 'GCL');

            if (!$apiKey) {
                throw new \Exception('SMS API configuration missing');
            }

            // Example API call - replace with your SMS provider's API
            $response = Http::post('https://api.example-sms.com/send', [
                'api_key' => $apiKey,
                'sender_id' => $senderId,
                'phone' => $this->formatPhoneNumber($phone),
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent successfully to {$phone}");
                return true;
            } else {
                Log::error("SMS API failed", ['response' => $response->body()]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error("SMS sending failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log SMS for development/testing
     */
    private function logSms(string $phone, string $message): bool
    {
        Log::info("SMS would be sent", [
            'phone' => $phone,
            'message' => $message,
        ]);
        return true;
    }

    /**
     * Format phone number for SMS sending
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // If it starts with 0, replace with +880
        if (str_starts_with($phone, '0')) {
            $phone = '+880' . substr($phone, 1);
        }
        // If it doesn't start with +880, add it
        elseif (!str_starts_with($phone, '+880')) {
            $phone = '+880' . $phone;
        }

        return $phone;
    }
}