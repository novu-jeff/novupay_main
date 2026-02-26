<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\MorongBill;
use App\Models\MorongHitpayTransaction;

class NovuStreamMorongPaymentController extends Controller
{
    /**
     * Creates HitPay Payment Request for Morong.
     * Stores readings and transaction in morong DB (reference_no = idempotent key for sync to morong app).
     */
    public function createHitpay(array $payload)
    {
        try {
            if (empty($payload['reference_no']) || empty($payload['amount'])) {
                Log::warning('Morong Missing required fields', $payload);
                return ['error' => 'Missing required fields'];
            }

            $reference_no = trim($payload['reference_no']);
            $account_no   = trim($payload['account_no']);
            $amount       = (float) $payload['amount'];
            $present_reading = $payload['present_reading'] ?? null;
            $previous_reading = $payload['previous_reading'] ?? null;
            $is_high_consumption = (int) ($payload['is_high_consumption'] ?? 0);

            $payor  = $payload['name'] ?? "Morong Customer";
            $email  = $payload['email'] ?? "morongbataanwd@gmail.com";
            $mobile = $payload['contact_no'] ?? null;

            try {
                $dueDate = !empty($payload['due_date'])
                    ? Carbon::parse($payload['due_date'])
                    : Carbon::now()->addDays(15);
            } catch (\Exception $e) {
                $dueDate = Carbon::now()->addDays(15);
            }
            $due_string = $dueDate->format('Y-m-d H:i:s');

            $qrph_fee  = $amount <= 2000 ? 20 : ($amount * 0.01);
            $gcash_fee = $amount * 0.023;
            $novupay_fee = 10;
            if ($amount < 800) {
                $selected_fee = $gcash_fee;
                $paymentMethods = ['gcash'];
            } else {
                $selected_fee = $qrph_fee;
                $paymentMethods = ['qrph_netbank', 'upay_online'];
            }
            $hitpay_fee = round($selected_fee, 1);
            $admin_fee  = $hitpay_fee + $novupay_fee;
            $final_amount = round($amount + $admin_fee, 2);
            if (env('NOVUPAY_FINAL_AMOUNT') === 'Testing') {
                $final_amount = 1;
            }

            $purpose = "Amount Due: ₱" . number_format($amount, 2) . "\n" .
                       "Convenience Fee: ₱" . number_format($admin_fee, 2) . "\n" .
                       "Bill Payment for {$account_no}";

            $hitpayPayload = [
                'amount'           => $final_amount,
                'currency'         => 'PHP',
                'email'            => $email,
                'purpose'          => $purpose,
                'reference_number' => $reference_no,
                'redirect_url'     => env('HITPAY_REDIRECT_URL'),
                'webhook'          => env('HITPAY_WEBHOOK_URL'),
                'name'             => $payor,
                'send_email'       => true,
                'send_sms'         => true,
                'add_admin_fee'    => true,
                'admin_fee'        => $admin_fee,
                'expiry_date'      => $due_string,
                'payment_methods'  => $paymentMethods,
            ];

            Log::info('Morong HitPay Payload', $hitpayPayload);

            $response = Http::withHeaders([
                'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
            ])->post(env('HITPAY_API_URL') . '/payment-requests', $hitpayPayload);

            if ($response->failed()) {
                Log::error('Morong HitPay FAILED', ['body' => $response->body()]);
                return ['error' => 'HitPay API failed'];
            }

            $hitpayData = $response->json();
            Log::info('Morong HitPay Response', $hitpayData);

            $txn = MorongHitpayTransaction::create([
                'reference_no'      => $reference_no,
                'hitpay_id'         => $hitpayData['id'] ?? null,
                'payment_request_id'=> $hitpayData['payment_request_id'] ?? null,
                'payment_url'       => $hitpayData['url'] ?? null,
                'amount'            => $amount,
                'convenience_fee'   => $admin_fee,
                'final_amount'      => $final_amount,
                'status'            => 'initiated',
                'request_payload'   => $hitpayPayload,
                'response_payload'  => $hitpayData,
                'initiated_at'      => now(),
            ]);

            $payloadWithPayor = array_merge($payload, [
                'payor' => $payor,
                'customer' => ['name' => $payor, 'email' => $email],
            ]);
            MorongBill::updateOrCreate(
                ['reference_no' => $reference_no],
                [
                    'account_no'      => $account_no,
                    'payor'           => $payor,
                    'email'           => $email,
                    'contact_no'      => $mobile,
                    'amount'          => $amount,
                    'previous_reading' => $previous_reading,
                    'present_reading' => $present_reading,
                    'is_high_consumption' => $is_high_consumption,
                    'status'          => 'initiated',
                    'payload'         => $payloadWithPayor,
                    'initiated_at'    => now(),
                ]
            );

            return [
                'success' => true,
                'hitpay'  => $hitpayData,
                'transaction' => $txn,
            ];
        } catch (\Throwable $e) {
            Log::channel('sync')->error('Morong HitPay Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reference_no' => $payload['reference_no'] ?? null,
            ]);
            Log::error('Morong HitPay Exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * GET /api/novustream/morong/payment-request?rn=...&an=...&am=...&pr=...&prev=...&hc=...
     * QR scan → store readings + transaction in morong DB → redirect to HitPay.
     */
    public function handle(Request $request)
    {
        Log::info('NovuStream API request received', [
            'merchant' => 'morong',
            'method'   => $request->method(),
            'path'     => $request->path(),
            'query'    => [
                'rn'   => $request->rn,
                'an'   => $request->an,
                'am'   => $request->am,
                'pr'   => $request->pr,
                'prev' => $request->prev,
                'hc'   => $request->hc,
            ],
        ]);
        Log::channel('sync')->info('Morong QR Scanned', $request->all());

        try {
            $rn = $request->rn;
            $an = $request->an;
            $am = $request->am;

            if (!$rn || !$an || !$am) {
                Log::warning('NovuStream Morong: validation failed', [
                    'reason' => 'missing_parameters',
                    'has_rn' => !empty($rn),
                    'has_an' => !empty($an),
                    'has_am' => !empty($am),
                ]);
                return response()->json(['error' => 'Missing parameters'], 400);
            }

            Log::info('NovuStream Morong: creating HitPay payment request', [
                'reference_no' => $rn,
                'account_no'   => $an,
                'amount'       => (float) $am,
            ]);

            $payload = [
                'reference_no'        => $rn,
                'account_no'          => $an,
                'amount'              => (float) $am,
                'present_reading'     => $request->pr,
                'previous_reading'   => $request->prev,
                'is_high_consumption' => $request->hc ?? 0,
                'name'                => $request->name,
                'email'               => $request->email,
                'contact_no'          => $request->contact_no ?? $request->mobile ?? null,
            ];

            $result = $this->createHitpay($payload);

            if (!empty($result['error'])) {
                Log::warning('NovuStream Morong: HitPay creation failed', [
                    'reference_no' => $rn,
                    'error'        => $result['error'],
                ]);
                return response()->json($result, 500);
            }

            $url = $result['hitpay']['url'] ?? null;
            if ($url) {
                Log::info('NovuStream Morong: redirecting to HitPay', [
                    'reference_no' => $rn,
                    'hitpay_url'   => $url,
                ]);
                return redirect()->away($url);
            }

            Log::warning('NovuStream Morong: no HitPay URL in response', ['reference_no' => $rn]);
            return response()->json(['error' => 'No HitPay URL'], 500);
        } catch (\Throwable $e) {
            Log::channel('sync')->error('Morong handle exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);
            Log::error('NovuStream Morong: exception', [
                'reference_no' => $request->rn ?? null,
                'error'        => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
