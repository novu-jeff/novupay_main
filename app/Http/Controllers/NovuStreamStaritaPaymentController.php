<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\StaritaBill;
use App\Models\StaritaHitpayTransaction;

class NovuStreamStaritaPaymentController extends Controller
{
    /**
     * Creates HitPay Payment Request for Starita
     */
    public function createHitpay(array $payload)
    {
        try {
            // ----------------------------------------------------
            // 1. Validate
            // ----------------------------------------------------
            if (empty($payload['reference_no']) || empty($payload['amount'])) {
                Log::warning('Starita Missing required fields', $payload);
                return ['error' => 'Missing required fields'];
            }

            // ----------------------------------------------------
            // 2. Extract inputs
            // ----------------------------------------------------
            $reference_no = trim($payload['reference_no']);
            $account_no   = trim($payload['account_no']);
            $amount       = (float) $payload['amount'];

            $present_reading = $payload['present_reading'] ?? null;
            $is_high_consumption = (int) ($payload['is_high_consumption'] ?? 0);

            $payor  = $payload['name'] ?? "Starita Customer";
            $email  = $payload['email'] ?? "srwdsystem2023@gmail.com";
            $mobile = $payload['contact_no'] ?? null;
            ['payor' => $payor, 'email' => $email, 'contact_no' => $mobile] =
                $this->resolvePayorDetails($account_no, $payor, $email, $mobile);

            // ----------------------------------------------------
            // 3. Due date fallback: +15 days
            // ----------------------------------------------------
            try {
                $dueDate = !empty($payload['due_date'])
                    ? Carbon::parse($payload['due_date'])
                    : Carbon::now()->addDays(15);
            } catch (\Exception $e) {
                $dueDate = Carbon::now()->addDays(15);
            }

            $due_string = $dueDate->format('Y-m-d H:i:s');

            // ----------------------------------------------------
            // 4. Compute fees
            // ----------------------------------------------------
            $qrph_fee  = $amount <= 2000 ? 20 : ($amount * 0.01);
            $gcash_fee = $amount * 0.023;

            $novupay_fee = 10; // production
            $novupay_fee_dummy = 0; // keep 0 for testing

            // Determine payment method group
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
            
            // Apply testing mode if enabled
            if (env('NOVUPAY_FINAL_AMOUNT') === 'Testing') {
                $final_amount = 1;
            }

            // ----------------------------------------------------
            // 5. HitPay Payload
            // ----------------------------------------------------
            $purpose = "Amount Due: ₱" . number_format($amount, 2) . "\n" .
                       "Convenience Fee: ₱" . number_format($admin_fee, 2) . "\n" .
                       "Bill Payment for {$account_no}";

            // Unique reference prefix for Starita
            $hp_reference = "NST-SRWD-" . $reference_no;

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

            Log::info('Starita HitPay Payload', $hitpayPayload);

            // ----------------------------------------------------
            // 6. Call HitPay API
            // ----------------------------------------------------
            $response = Http::withHeaders([
                'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
            ])->post(env('HITPAY_API_URL') . '/payment-requests', $hitpayPayload);

            if ($response->failed()) {
                Log::error('Starita HitPay FAILED', ['body' => $response->body()]);
                return ['error' => 'HitPay API failed'];
            }

            $hitpayData = $response->json();
            Log::info('Starita HitPay Response', $hitpayData);

            // ----------------------------------------------------
            // 7. Save HitPay Record
            // ----------------------------------------------------
            $txn = StaritaHitpayTransaction::create([
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

            // ----------------------------------------------------
            // 8. Save Bill (Starita)
            // ----------------------------------------------------
            $payloadWithPayor = array_merge($payload, [
                'payor' => $payor,
                'customer' => ['name' => $payor, 'email' => $email],
            ]);

            $existingBill = StaritaBill::where('reference_no', $reference_no)->first();
            $existingIsPaid = $existingBill && (
                strtolower((string) $existingBill->status) === 'paid' || !is_null($existingBill->paid_at)
            );

            $billData = [
                'account_no'      => $account_no,
                'payor'           => $payor,
                'email'           => $email,
                'contact_no'      => $mobile,
                'amount'          => $amount,
                'present_reading' => $present_reading,
                'is_high_consumption' => $is_high_consumption,
                // Preserve paid records; never downgrade to initiated on QR re-scan.
                'status'          => $existingIsPaid ? 'paid' : 'initiated',
                'payload'         => $payloadWithPayor,
                'initiated_at'    => $existingBill?->initiated_at ?? now(),
            ];
            if ($existingIsPaid) {
                $billData['paid_at'] = $existingBill?->paid_at ?? now();
            }
            if (Schema::connection('starita')->hasColumn('starita_bills', 'hitpay_reference')) {
                $billData['hitpay_reference'] = $existingBill?->hitpay_reference ?? ($hitpayData['id'] ?? null);
            }
            if (Schema::connection('starita')->hasColumn('starita_bills', 'hitpay_url')) {
                $billData['hitpay_url'] = $existingBill?->hitpay_url ?? ($hitpayData['url'] ?? null);
            }
            StaritaBill::updateOrCreate(['reference_no' => $reference_no], $billData);

            return [
                'success' => true,
                'hitpay'  => $hitpayData,
                'transaction' => $txn,
            ];

        } catch (\Throwable $e) {
            Log::channel('sync')->error('Starita HitPay Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reference_no' => $payload['reference_no'] ?? null,
            ]);
            Log::error('Starita HitPay Exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * GET /api/novustream/starita/payment-request?rn=...&an=...&am=...&pr=...&hc=...
     * QR scan → create HitPay request → redirect to HitPay checkout.
     */
    public function handle(Request $request)
    {
        Log::info('NovuStream API request received', [
            'merchant' => 'starita',
            'method'   => $request->method(),
            'path'     => $request->path(),
            'query'    => [
                'rn' => $request->rn,
                'an' => $request->an,
                'am' => $request->am,
                'pr' => $request->pr,
                'hc' => $request->hc,
            ],
        ]);

        $rn = $request->rn;
        $an = $request->an;
        $am = $request->am;

        if (!$rn || !$an || !$am) {
            Log::warning('NovuStream Starita: validation failed', [
                'reason' => 'missing_parameters',
                'has_rn' => !empty($rn),
                'has_an' => !empty($an),
                'has_am' => !empty($am),
            ]);
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        Log::info('NovuStream Starita: creating HitPay payment request', [
            'reference_no' => $rn,
            'account_no'   => $an,
            'amount'       => (float) $am,
        ]);

        $payload = [
            'reference_no'        => $rn,
            'account_no'          => $an,
            'amount'              => (float) $am,
            'present_reading'     => $request->pr,
            'is_high_consumption' => $request->hc ?? 0,
            'name'                => $request->name,
            'email'               => $request->email,
            'contact_no'          => $request->contact_no ?? $request->mobile ?? null,
        ];

        $result = $this->createHitpay($payload);

        if (!empty($result['error'])) {
            Log::warning('NovuStream Starita: HitPay creation failed', [
                'reference_no' => $rn,
                'error'        => $result['error'],
            ]);
            return response()->json($result, 500);
        }

        $url = $result['hitpay']['url'] ?? null;
        if ($url) {
            Log::info('NovuStream Starita: redirecting to HitPay', [
                'reference_no' => $rn,
                'hitpay_url'   => $url,
            ]);
            return redirect()->away($url);
        }

        Log::warning('NovuStream Starita: no HitPay URL in response', ['reference_no' => $rn]);
        return response()->json(['error' => 'No HitPay URL'], 500);
    }

    private function resolvePayorDetails(string $accountNo, ?string $payor, ?string $email, ?string $contactNo): array
    {
        $resolved = [
            'payor' => $payor ?: 'Starita Customer',
            'email' => $email ?: 'srwdsystem2023@gmail.com',
            'contact_no' => $contactNo,
        ];

        if ($accountNo === '') {
            return $resolved;
        }

        try {
            if (!Schema::connection('starita_app')->hasTable('concessioner_accounts')) {
                return $resolved;
            }

            $accountColumns = Schema::connection('starita_app')->getColumnListing('concessioner_accounts');
            $contactColumn = collect(['contact_no', 'mobile_no', 'contact_number', 'phone'])
                ->first(fn ($c) => in_array($c, $accountColumns, true));
            $hasUserId = in_array('user_id', $accountColumns, true);
            $userContactColumn = null;
            if (Schema::connection('starita_app')->hasTable('users')) {
                $userColumns = Schema::connection('starita_app')->getColumnListing('users');
                $userContactColumn = collect(['contact_no', 'mobile_no', 'contact_number', 'phone'])
                    ->first(fn ($c) => in_array($c, $userColumns, true));
            }

            $query = DB::connection('starita_app')->table('concessioner_accounts as ca')
                ->where('ca.account_no', $accountNo);

            if ($hasUserId && Schema::connection('starita_app')->hasTable('users')) {
                $query->leftJoin('users as u', 'u.id', '=', 'ca.user_id');
                $query->addSelect('u.name as user_name', 'u.email as user_email');
                if ($userContactColumn) {
                    $query->addSelect("u.{$userContactColumn} as user_contact");
                }
            }

            if ($contactColumn) {
                $query->addSelect("ca.{$contactColumn} as account_contact");
            }

            $row = $query->first();
            if (!$row) {
                return $resolved;
            }

            if (empty($payor) && !empty($row->user_name)) {
                $resolved['payor'] = (string) $row->user_name;
            }
            if (empty($email) && !empty($row->user_email)) {
                $resolved['email'] = (string) $row->user_email;
            }
            if (empty($contactNo) && !empty($row->account_contact)) {
                $resolved['contact_no'] = (string) $row->account_contact;
            } elseif (empty($contactNo) && !empty($row->user_contact)) {
                $resolved['contact_no'] = (string) $row->user_contact;
            }
        } catch (\Throwable $e) {
            Log::warning('NovuStream Starita: failed to enrich payor details', [
                'account_no' => $accountNo,
                'error' => $e->getMessage(),
            ]);
        }

        return $resolved;
    }
}
