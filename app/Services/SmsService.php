<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    private string $provider;

    public function __construct()
    {
        // falls back to env('SMS_PROVIDER', 'log') if not set in config
        $this->provider = config('services.sms.provider', env('SMS_PROVIDER', 'log'));
    }

    /**
     * Send a generic SMS message.
     *
     * @return array{ok:bool, provider:string, http_status:int|null, trxid:?string, error:?string, raw:mixed}
     */
    public function send(string $phone, string $message): array
    {
        $to = $this->normalizeMsisdn($phone);

        return match ($this->provider) {
            'mimsms' => $this->sendViaMimSms($to, $message),
            'log'    => $this->logOnly($to, $message),
            default  => $this->logOnly($to, $message),
        };
    }

    /**
     * Helper for OTP messages.
     */
    public function sendOtp(string $phone, string $otp, string $brand = 'GCL Tender Verification'): array
    {
        $msg = "Your {$brand} OTP: {$otp}. Valid for 5 minutes. Do not share this code.";
        return $this->send($phone, $msg);
    }

    /**
     * Check SMS balance (MiMSMS).
     *
     * @return array{ok:bool, provider:string, http_status:int|null, balance:int|null, error:?string, raw:mixed}
     */
    public function balance(): array
    {
        if ($this->provider !== 'mimsms') {
            return [
                'ok' => true, 'provider' => $this->provider, 'http_status' => null,
                'balance' => null, 'error' => null, 'raw' => null,
            ];
        }

        $apiKey = (string) config('services.mimsms.api_key');

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->retry(3, 250, throw: false)
                ->get('https://api.mimsms.com/api/v1/get-balance', [
                    'api_key' => $apiKey,
                ]);

            $json = $response->json();

            if ($response->successful() && isset($json['status']) && Str::upper($json['status']) === 'OK') {
                return [
                    'ok' => true,
                    'provider' => 'mimsms',
                    'http_status' => $response->status(),
                    'balance' => (int) ($json['balance'] ?? 0),
                    'error' => null,
                    'raw' => $json,
                ];
            }

            return [
                'ok' => false,
                'provider' => 'mimsms',
                'http_status' => $response->status(),
                'balance' => null,
                'error' => $json['message'] ?? 'Balance check failed',
                'raw' => $json,
            ];
        } catch (ConnectionException $e) {
            Log::warning('SMS balance connection error', ['provider' => 'mimsms', 'error' => $e->getMessage()]);
            return [
                'ok' => false,
                'provider' => 'mimsms',
                'http_status' => null,
                'balance' => null,
                'error' => 'Network error while checking balance',
                'raw' => null,
            ];
        }
    }

    // ---------- Providers ----------

    private function sendViaMimSms(string $phone, string $message): array
    {
        $apiKey  = (string) config('services.mimsms.api_key');
        $sender  = (string) config('services.mimsms.sender_name', 'GCL');
        $txType  = (string) config('services.mimsms.transaction_type', 'T');

        // Auto-detect unicode if needed
        $isUnicode = $this->containsUnicode($message);
        $type      = $isUnicode ? 'unicode' : $txType;

        $payload = [
            'api_key'   => $apiKey,
            'type'      => $type,           // 'T' or 'unicode'
            'contacts'  => $phone,          // single or comma-separated
            'senderid'  => $sender,
            'msg'       => $message,
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(20)               // fail fast
                ->retry(3, 300, throw: false) // simple resiliency
                ->post('https://api.mimsms.com/smsapi', $payload);

            $json = $response->json();

            // Success shape normally: { statusCode: 200, status: "OK", tranid: "...", responseResult: "..." }
            if ($response->successful() && isset($json['status']) && Str::upper($json['status']) === 'OK') {
                return [
                    'ok' => true,
                    'provider' => 'mimsms',
                    'http_status' => $response->status(),
                    'trxid' => $json['tranid'] ?? null,
                    'error' => null,
                    'raw' => $json,
                ];
            }

            // Log with safe context (never log api_key or PII beyond what’s necessary)
            Log::warning('SMS send failed via MiMSMS', [
                'http_status' => $response->status(),
                'provider' => 'mimsms',
                'code' => $json['statusCode'] ?? null,
                'status' => $json['status'] ?? null,
                'message' => $json['message'] ?? null,
            ]);

            return [
                'ok' => false,
                'provider' => 'mimsms',
                'http_status' => $response->status(),
                'trxid' => $json['tranid'] ?? null,
                'error' => $json['message'] ?? 'MiMSMS error',
                'raw' => $json,
            ];
        } catch (ConnectionException $e) {
            Log::error('SMS network error via MiMSMS', ['error' => $e->getMessage()]);
            return [
                'ok' => false,
                'provider' => 'mimsms',
                'http_status' => null,
                'trxid' => null,
                'error' => 'Network error while contacting MiMSMS',
                'raw' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('SMS unexpected error via MiMSMS', ['error' => $e->getMessage()]);
            return [
                'ok' => false,
                'provider' => 'mimsms',
                'http_status' => null,
                'trxid' => null,
                'error' => 'Unexpected error while sending SMS',
                'raw' => null,
            ];
        }
    }

    private function logOnly(string $phone, string $message): array
    {
        Log::info('SMS[LOG]: message not sent to gateway (log provider)', [
            'to' => $phone,
            'len' => Str::length($message),
        ]);

        return [
            'ok' => true,
            'provider' => 'log',
            'http_status' => null,
            'trxid' => 'LOG-' . Str::random(8),
            'error' => null,
            'raw' => null,
        ];
    }

    // ---------- Helpers ----------

    /**
     * Normalize BD numbers.
     * Accepts "01XXXXXXXXX", "+8801XXXXXXXXX", "8801XXXXXXXXX" → returns "01XXXXXXXXX" by default.
     * Change behavior to your gateway’s preferred format if needed.
     */
    private function normalizeMsisdn(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        // If starts with 8801XXXXXXXXX, convert to 01XXXXXXXXX (MiMSMS accepts both; keep local format)
        if (Str::startsWith($digits, '8801') && strlen($digits) === 13) {
            return '0' . substr($digits, 3);
        }

        // If already 01XXXXXXXXX, keep as is
        if (Str::startsWith($digits, '01') && strlen($digits) === 11) {
            return $digits;
        }

        // Fallback: return original trimmed (let gateway validate)
        return ltrim($phone);
    }

    private function containsUnicode(string $text): bool
    {
        // Detect any non-GSM-7 char
        return !preg_match('//u', $text) || strlen($text) !== mb_strlen($text, 'UTF-8');
    }
}
