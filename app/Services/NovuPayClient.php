<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NovuPayClient
{
    public function apiKey(): string
    {
        return (string) config('novupay.api_key');
    }

    public function clientId(): string
    {
        return (string) config('novupay.client_id');
    }

    public function clientSecret(): string
    {
        return (string) config('novupay.client_secret');
    }

    public function apiBase(): string
    {
        return rtrim((string) config('novupay.api_base', 'https://api.novu-pay.com'), '/');
    }

    public function apiResolveIp(): string
    {
        return trim((string) config('novupay.api_resolve_ip', ''));
    }

    public function hostedBase(): string
    {
        return rtrim((string) config('novupay.hosted_base', 'https://novu-pay.com'), '/');
    }

    /**
     * HTTP client that can force api.novu-pay.com to an internal IP (LAN hairpin).
     */
    protected function http()
    {
        $pending = Http::acceptJson()->withOptions(['timeout' => 30]);
        $resolveIp = $this->apiResolveIp();
        if ($resolveIp === '' || !filter_var($resolveIp, FILTER_VALIDATE_IP)) {
            return $pending;
        }

        $host = parse_url($this->apiBase(), PHP_URL_HOST) ?: 'api.novu-pay.com';
        $port = (int) (parse_url($this->apiBase(), PHP_URL_PORT) ?: 443);

        return $pending->withOptions([
            'curl' => [
                CURLOPT_RESOLVE => ["{$host}:{$port}:{$resolveIp}"],
            ],
        ]);
    }

    public function notificationUrl(): string
    {
        return (string) config(
            'novupay.notification_url',
            'https://portal.staritawaterdistrictpamp.gov.ph/webhooks/novupay'
        );
    }

    public function redirectUrl(): string
    {
        return (string) config(
            'novupay.redirect_url',
            'https://portal.staritawaterdistrictpamp.gov.ph/payments/novupay/return'
        );
    }

    public function minAmount(): float
    {
        return (float) config('novupay.min_amount', 50.00);
    }

    public function makeReqId(string $referenceNo): string
    {
        $base = preg_replace('/[^A-Za-z0-9_\-]/', '', $referenceNo) ?: 'ORDER';
        return $base . '-' . Str::lower(Str::random(10));
    }

    public function buildCheckoutUrl(string $uid): string
    {
        return $this->hostedBase() . '/checkout/' . rawurlencode($uid) . '/';
    }

    public function normalizeContact(?string $contact): ?string
    {
        if ($contact === null || trim($contact) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $contact);
        if ($digits === null || $digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '63') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        }

        if (Str::startsWith($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        if (preg_match('/^9\d{9}$/', $digits)) {
            return $digits;
        }

        return null;
    }

    public function expiryHours(?string $dueDate): int
    {
        if (empty($dueDate)) {
            return 24;
        }

        try {
            $hours = (int) ceil(now('Asia/Manila')->diffInMinutes(
                \Carbon\Carbon::parse($dueDate)->timezone('Asia/Manila')->endOfDay(),
                false
            ) / 60);
        } catch (\Throwable $e) {
            return 24;
        }

        if ($hours < 2) {
            return 2;
        }

        if ($hours > 720) {
            return 720;
        }

        return $hours;
    }

    public static function expectedSignature(string $reqId, string $status, string $clientSecret): string
    {
        return sha1($reqId . $status . '{' . $clientSecret . '}');
    }

    public function verifyWebhookSignature(string $reqId, string $status, string $signature): bool
    {
        $secret = $this->clientSecret();
        if ($secret === '' || $reqId === '' || $status === '' || $signature === '') {
            return false;
        }

        $expected = self::expectedSignature($reqId, $status, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * @return array{ok: bool, status?: int, data?: array, error?: string}
     */
    public function createCheckout(array $payload): array
    {
        $apiKey = $this->apiKey();
        $clientId = $this->clientId();

        if ($apiKey === '' || $clientId === '') {
            return ['ok' => false, 'error' => 'NovuPay credentials are not configured.'];
        }

        $amount = round((float) ($payload['amount'] ?? 0), 2);
        if ($amount < $this->minAmount()) {
            return [
                'ok' => false,
                'error' => 'Amount must be at least PHP ' . number_format($this->minAmount(), 2) . '.',
            ];
        }

        $body = [
            'req_id' => (string) $payload['req_id'],
            'client_id' => $clientId,
            'amount' => number_format($amount, 2, '.', ''),
            'description' => (string) ($payload['description'] ?? 'Sta. Rita Water District bill'),
            'email' => (string) ($payload['email'] ?? 'srwdsystem2023@gmail.com'),
            'notification_url' => (string) ($payload['notification_url'] ?? $this->notificationUrl()),
            'redirect_url' => (string) ($payload['redirect_url'] ?? $this->redirectUrl()),
            'enabled_channels' => ['qrph'],
            'expiry' => (int) ($payload['expiry'] ?? 24),
        ];

        if (!empty($payload['name'])) {
            $body['name'] = (string) $payload['name'];
        }

        $contact = $this->normalizeContact($payload['contact'] ?? null);
        if ($contact) {
            $body['contact'] = $contact;
        }

        if (!empty($payload['param1'])) {
            $body['param1'] = (string) $payload['param1'];
        }

        if (!empty($payload['param2'])) {
            $body['param2'] = (string) $payload['param2'];
        }

        try {
            $response = $this->http()->withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $apiKey,
            ])->timeout(30)->post($this->apiBase() . '/api/open/checkout/', $body);

            $json = $response->json() ?? [];
            if ($response->successful() && strtolower((string) ($json['status'] ?? '')) === 'success'
                && !empty($json['checkout_url']) && !empty($json['uid'])) {
                return [
                    'ok' => true,
                    'status' => $response->status(),
                    'data' => [
                        'id' => $json['id'] ?? null,
                        'uid' => (string) $json['uid'],
                        'checkout_url' => (string) $json['checkout_url'],
                        'req_id' => (string) $payload['req_id'],
                        'amount' => $amount,
                    ],
                ];
            }

            $this->safeLog('warning', 'NovuPay checkout create failed', [
                'http_status' => $response->status(),
                'req_id' => $payload['req_id'] ?? null,
                'api_status' => $json['status'] ?? null,
                'message' => $json['message'] ?? $json['error'] ?? null,
            ]);

            return [
                'ok' => false,
                'status' => $response->status(),
                'error' => (string) ($json['message'] ?? $json['error'] ?? 'Unable to create NovuPay checkout.'),
                'data' => $json,
            ];
        } catch (\Throwable $e) {
            $this->safeLog('error', 'NovuPay checkout exception', [
                'req_id' => $payload['req_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => 'Unable to reach NovuPay checkout API.'];
        }
    }

    private function safeLog(string $level, string $message, array $context = []): void
    {
        try {
            Log::{$level}($message, $context);
        } catch (\Throwable $e) {
            // Ignore logging failures (e.g. unwritable laravel.log).
        }
    }

    /**
     * @return array{ok: bool, status?: int, data?: array, error?: string}
     */
    public function checkStatus(string $reqId): array
    {
        $apiKey = $this->apiKey();
        $clientId = $this->clientId();

        if ($apiKey === '' || $clientId === '' || $reqId === '') {
            return ['ok' => false, 'error' => 'Missing NovuPay credentials or req_id.'];
        }

        try {
            $response = $this->http()->withHeaders([
                'x-api-key' => $apiKey,
            ])->timeout(20)->get($this->apiBase() . '/api/check_code/', [
                'client_id' => $clientId,
                'req_id' => $reqId,
                'mode' => 'API',
            ]);

            $json = $response->json() ?? [];
            if ($response->successful() && !empty($json['status'])) {
                return [
                    'ok' => true,
                    'status' => $response->status(),
                    'data' => $json,
                ];
            }

            return [
                'ok' => false,
                'status' => $response->status(),
                'error' => (string) ($json['message'] ?? $json['error'] ?? 'Status not available.'),
                'data' => $json,
            ];
        } catch (\Throwable $e) {
            $this->safeLog('error', 'NovuPay check status exception', [
                'req_id' => $reqId,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'error' => 'Unable to reach NovuPay status API.'];
        }
    }
}
