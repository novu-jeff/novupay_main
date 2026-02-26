<?php

namespace App\Services;

use App\Models\KaelcoBill;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KaelcoWebhookNotifier
{
    /**
     * Notify Kaelco that a payment was completed (dynamic URL from config, payment_method from payload).
     * Call this from both the HitPay webhook handler and the payment confirmation page.
     */
    public static function notifyPaymentCompleted(KaelcoBill $bill, ?string $paymentMethod = null): bool
    {
        $url = config('services.kaelco.webhook_url');
        if (empty($url)) {
            Log::warning('KAELCO Webhook: URL not configured (services.kaelco.webhook_url)');
            return false;
        }

        $method = $paymentMethod ?? self::extractPaymentMethodFromPayload($bill);

        $payload = [
            'transaction_id'   => $bill->hitpay_reference ?? '',
            'reference_number' => $bill->reference_no,
            'status'           => 'completed',
            'payment_method'   => $method,
        ];

        try {
            $response = Http::asForm()->post($url, $payload);
            Log::info('KAELCO Webhook called', [
                'url'      => $url,
                'payload'  => $payload,
                'response' => $response->body(),
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('KAELCO Webhook Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get actual payment channel from bill payload (payments[].payment_type from HitPay webhook).
     */
    public static function extractPaymentMethodFromPayload(KaelcoBill $bill): string
    {
        $payload = $bill->payload;
        if (!is_array($payload)) {
            $payload = is_string($payload) ? (json_decode($payload, true) ?? []) : [];
        }
        $payments = $payload['payments'] ?? [];
        if (is_array($payments)) {
            foreach ($payments as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $status = strtolower((string) ($p['status'] ?? ''));
                if (!in_array($status, ['succeeded', 'completed'], true)) {
                    continue;
                }
                $type = $p['payment_type'] ?? $p['payment_method'] ?? $p['method'] ?? null;
                if ($type !== null && $type !== '') {
                    return (string) $type;
                }
            }
        }
        return $payload['payment_method'] ?? $payload['method'] ?? $payload['by_method'] ?? 'Online';
    }
}
