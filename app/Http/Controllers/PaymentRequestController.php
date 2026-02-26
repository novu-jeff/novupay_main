<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Bill;
use Carbon\Carbon;

class PaymentRequestController extends Controller
{
    /**
     * Pelco → Novupay
     * Creates HitPay payment request from decrypted payload
     */
    public function apiCreateHitpayPaymentRequest(array $payload)
    {
        try {
            // ----------------------------------------------------
            // 1. Validate minimum required fields
            // ----------------------------------------------------
            if (empty($payload['reference_no']) || empty($payload['amount'])) {
                Log::warning('Missing required fields', $payload);
                return ['error' => 'Missing required fields: reference_no or amount'];
            }

            // ----------------------------------------------------
            // 2. Extract Inputs
            // ----------------------------------------------------
            $reference_no = trim($payload['reference_no']);
            $account_no   = trim($payload['account_no'] ?? '000000');
            $payor        = trim($payload['name'] ?? 'Novupay Customer');
            $email        = trim($payload['email'] ?? 'noreply@novupay.com');
            $contact_no   = trim($payload['contact_no'] ?? '');
            $arrears      = (float) ($payload['arrears'] ?? 0);

            // Amount must be float
            $amount       = (float) $payload['amount'];

            // Billing data
            $prev_reading = $payload['prev_reading'] ?? null;
            $present_reading = $payload['present_reading'] ?? null;
            $billing_period_from = $payload['billing_period_from'] ?? null;
            $billing_period_to   = $payload['billing_period_to'] ?? null;

            // ----------------------------------------------------
            // 3. Due Date (Fallback: +15 days)
            // ----------------------------------------------------
            try {
                $due_date = !empty($payload['due_date'])
                    ? Carbon::parse($payload['due_date'])
                    : Carbon::now()->addDays(15);
            } catch (\Exception $e) {
                $due_date = Carbon::now()->addDays(15);
            }

            $due_date_string = $due_date->format('Y-m-d H:i:s');

            // ----------------------------------------------------
            // 4. Fee Computation
            // ----------------------------------------------------
            $qrph_fee  = $amount <= 2000 ? 20 : ($amount * 0.01);
            $gcash_fee = $amount * 0.023;

            $novupay_fee = 8.8;
            $novupay_feeDummy = 0; // For testing purposes

            // Select default fee & methods based on amount
            if ($amount < 800) {
                $selected_fee   = $gcash_fee;
                $paymentMethods = ['gcash'];
            } else {
                $selected_fee   = $qrph_fee;
                $paymentMethods = ['qrph_netbank', 'upay_online'];
            }

            // Allow callers (e.g. KAELCO) to override payment methods
            if (!empty($payload['payment_methods']) && is_array($payload['payment_methods'])) {
                $paymentMethods = $payload['payment_methods'];
            }

            $hitpay_fee = round($selected_fee, 1);
            $additional_service_fee = $hitpay_fee + $novupay_feeDummy;

            $final_amount = round($amount + $additional_service_fee, 2);
            
            // Apply testing mode if enabled
            if (env('NOVUPAY_FINAL_AMOUNT') === 'Testing') {
                $final_amount = 1;
            }

            // ----------------------------------------------------
            // 5. HitPay Payload
            // ----------------------------------------------------
            $purpose = "Amount Due: ₱" . number_format($amount, 2) . "\n" .
                       "Convenience Fee: ₱" . number_format($additional_service_fee, 2) . "\n" .
                       "Bill Payment for {$account_no}";
            $reference_no = $reference_no;
            $email = $payload['email'] ?? 'support@novupay.ph';
            $bill_month = $payload['bill_month'] ?? null;

            $hitpayPayload = [
                'amount'           => $final_amount,
                'currency'         => 'PHP',
                'email'            => $email, // TEMPORARY OVERRIDE
                'purpose'          => $purpose,
                'reference_number' => $reference_no,
                'redirect_url'     => env('HITPAY_REDIRECT_URL'),
                'webhook'          => env('HITPAY_WEBHOOK_URL'),
                'name'             => $payor,
                'send_email'       => true,
                'send_sms'         => true,
                'add_admin_fee'    => true,
                'admin_fee'        => $additional_service_fee,
                'expiry_date'      => $due_date_string,
                'description'      => "Bill Payment for Period of: {$bill_month}",
                'payment_methods'  => $paymentMethods,
            ];

            Log::info('Sending HitPay Request!!', $hitpayPayload);

            // ----------------------------------------------------
            // 6. Send HitPay Request
            // ----------------------------------------------------
            $response = Http::withHeaders([
                'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
            ])->post(env('HITPAY_API_URL') . '/payment-requests', $hitpayPayload);
            // dd($response->body());

            if ($response->failed()) {
                Log::error('HitPay API failed', ['response' => $response->body()]);
                return ['error' => 'Failed to contact HitPay'];
            }

            $hitpayData = $response->json();
            Log::info('HitPay Data', $hitpayData);

            // ----------------------------------------------------
            // 7. Create / Update BILL
            // ----------------------------------------------------
            $bill = Bill::updateOrCreate(
                ['reference_no' => $reference_no],
                [
                    'account_no'          => $account_no,
                    'payor'               => $payor,
                    'consumer_type'       => $hitpayData['consumer_type'] ?? 'residential',
                    'email'               => $email,
                    'contact_no'          => $contact_no,
                    'amount'              => $amount,
                    'arrears'             => $arrears,
                    'due_date'            => $due_date_string,
                    'prev_reading'        => $prev_reading,
                    'present_reading'     => $present_reading,
                    'billing_period_from' => $billing_period_from,
                    'billing_period_to'   => $billing_period_to,
                    'status'              => 'initiated',
                    'hitpay_reference'    => $hitpayData['id'] ?? null,
                    'hitpay_url'          => $hitpayData['url'] ?? null,
                    'payload'             => $hitpayData,
                    'initiated_at'        => now(),
                ]
            );

            return [
                'success' => true,
                'bill'    => $bill,
                'hitpay'  => $hitpayData,
            ];

        } catch (\Throwable $e) {
            Log::error('HitPay Request Exception', ['error' => $e->getMessage()]);
            return ['error' => 'Server Error'];
        }
    }



    /**
     * QR → decrypt → create bill → redirect to HitPay
     */
    public function handleQr(Request $request)
    {
        Log::info('Incoming encrypted QR data', ['data' => $request->query('data')]);

        $encryptedData = $request->query('data');

        if (!$encryptedData) {
            abort(400, 'Missing encrypted data');
        }

        $payload = decryptPayload($encryptedData);

        if (!$payload) {
            abort(403, 'Failed to decrypt payload');
        }

        Log::info('Decrypted QR Payload', $payload);

        $result = $this->apiCreateHitpayPaymentRequest($payload);

        if (!empty($result['error'])) {
            return response()->json($result, 500);
        }

        $hitpayUrl = $result['hitpay']['url'] ?? null;

        if ($hitpayUrl) {
            Log::info("Redirecting to HitPay: {$hitpayUrl}");
            return redirect()->away($hitpayUrl);
        }

        return response()->json(['error' => 'Failed to create HitPay request'], 500);
    }
}
