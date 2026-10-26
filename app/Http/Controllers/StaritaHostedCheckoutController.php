<?php

namespace App\Http\Controllers;

use App\Services\StaritaNovuPayCheckoutService;
use Illuminate\Support\Facades\Log;

class StaritaHostedCheckoutController extends Controller
{
    public function __construct(private StaritaNovuPayCheckoutService $checkoutService)
    {
    }

    /**
     * Live SOA QR / Pay Online entry: create NovuPay hosted checkout and redirect.
     */
    public function redirect(string $reference_no)
    {
        try {
            Log::info('Starita hosted checkout requested', [
                'reference_no' => $reference_no,
            ]);
        } catch (\Throwable $e) {
            // Ignore logging failures.
        }

        $result = $this->checkoutService->startForReference($reference_no);

        if (!empty($result['already_paid']) && !empty($result['redirect'])) {
            return redirect()->away((string) $result['redirect']);
        }

        if (!empty($result['voided']) && !empty($result['redirect'])) {
            return redirect()->away((string) $result['redirect']);
        }

        if (!empty($result['ok']) && !empty($result['checkout_url'])) {
            return redirect()->away((string) $result['checkout_url']);
        }

        try {
            Log::warning('Starita hosted checkout failed', [
                'reference_no' => $reference_no,
                'error' => $result['error'] ?? 'unknown',
            ]);
        } catch (\Throwable $e) {
            // Ignore logging failures.
        }

        return response()->view('payment.starita-checkout-unavailable', [
            'reference_no' => $reference_no,
            'message' => $result['error'] ?? 'Unable to start NovuPay checkout for this bill.',
        ], 502);
    }
}
