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
use App\Services\OverdueQrService;

class NovuStreamStaritaPaymentController extends Controller
{
    public function __construct(
        private readonly OverdueQrService $overdueQr
    ) {
    }

    /**
     * Creates HitPay Payment Request for Starita
     *
     * @param  array  $overdueMeta  optional keys: penalty_applied, penalty_amount
     */
    public function createHitpay(array $payload, array $overdueMeta = [])
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
            if (!$this->overdueQr->isPastDue($payload)) {
                $payload = $this->overdueQr->normalizeStaritaPayload($payload);
                $payload = $this->overdueQr->stripPenaltyIfBeforeDue($payload);
                $amount = (float) $payload['amount'];
            }
            $penaltyApplied = (bool) ($overdueMeta['penalty_applied'] ?? false);
            $penaltyAmount = (float) ($overdueMeta['penalty_amount'] ?? 0);

            $present_reading = $payload['present_reading'] ?? null;
            $is_high_consumption = (int) ($payload['is_high_consumption'] ?? 0);

            $payor  = $payload['name'] ?? "Starita Customer";
            $email  = $payload['email'] ?? null;
            $mobile = $payload['contact_no'] ?? null;
            ['payor' => $payor, 'email' => $email, 'contact_no' => $mobile] =
                $this->resolvePayorDetails($account_no, $payor, $email, $mobile);

            $existingBill = StaritaBill::where('reference_no', $reference_no)->first();
            if ($this->isStaritaBillPaid($existingBill)) {
                return $this->alreadyPaidHitpayResult($existingBill, $reference_no);
            }

            // ----------------------------------------------------
            // 3. Due date: payload / bill table, else +15 days
            // HitPay requires expiry_date STRICTLY after now (date-after-now).
            // Using bill due_date on the due date itself (or when past due with
            // penalty mode) causes: "The expiry date must be a date after now."
            // ----------------------------------------------------
            $resolvedDue = $this->overdueQr->resolveDueDate($payload);
            if ($resolvedDue) {
                $billDueEnd = $resolvedDue->copy()->timezone('Asia/Manila')->endOfDay();
            } else {
                try {
                    $billDueEnd = !empty($payload['due_date'])
                        ? Carbon::parse($payload['due_date'])->timezone('Asia/Manila')->endOfDay()
                        : Carbon::now('Asia/Manila')->addDays(15)->endOfDay();
                } catch (\Exception $e) {
                    $billDueEnd = Carbon::now('Asia/Manila')->addDays(15)->endOfDay();
                }
            }

            // Checkout window: keep bill due when still in the future; otherwise
            // extend so payment can complete today / after due (penalty mode).
            $minExpiry = Carbon::now('Asia/Manila')->addDay()->endOfDay();
            $dueDate = $billDueEnd->greaterThan($minExpiry) ? $billDueEnd : $minExpiry;
            $due_string = $dueDate->format('Y-m-d H:i:s');

            if ($dueDate->ne($billDueEnd)) {
                Log::info('Starita HitPay: extended expiry past bill due date', [
                    'reference_no' => $reference_no,
                    'bill_due_end' => $billDueEnd->format('Y-m-d H:i:s'),
                    'hitpay_expiry' => $due_string,
                ]);
            }

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

            $reusableTxn = $this->findReusableHitpayTransaction($reference_no, $final_amount);
            if ($reusableTxn) {
                Log::info('Starita HitPay reused existing payment request', [
                    'reference_no' => $reference_no,
                    'hitpay_id' => $reusableTxn->hitpay_id,
                    'final_amount' => $final_amount,
                ]);

                return [
                    'success' => true,
                    'reused' => true,
                    'hitpay' => [
                        'id' => $reusableTxn->hitpay_id,
                        'url' => $reusableTxn->payment_url,
                    ],
                    'transaction' => $reusableTxn,
                ];
            }

            // ----------------------------------------------------
            // 5. HitPay Payload
            // ----------------------------------------------------
            $purpose = "Amount Due: ₱" . number_format($amount, 2) . "\n";
            if ($penaltyApplied && $penaltyAmount > 0) {
                $purpose .= "Penalty: ₱" . number_format($penaltyAmount, 2) . "\n";
            }
            $purpose .= "Convenience Fee: ₱" . number_format($admin_fee, 2) . "\n" .
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
            $existingIsPaid = $this->isStaritaBillPaid($existingBill);

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
     * Save the scanned SOA bill without opening a HitPay checkout.
     * Used while online payment is on the maintenance page.
     */
    public function recordStaritaBill(array $payload): array
    {
        try {
            if (empty($payload['reference_no']) || empty($payload['amount'])) {
                Log::warning('Starita Missing required fields', $payload);
                return ['error' => 'Missing required fields'];
            }

            $reference_no = trim($payload['reference_no']);
            $account_no   = trim((string) ($payload['account_no'] ?? ''));
            $amount       = (float) $payload['amount'];
            if (!$this->overdueQr->isPastDue($payload)) {
                $payload = $this->overdueQr->normalizeStaritaPayload($payload);
                $payload = $this->overdueQr->stripPenaltyIfBeforeDue($payload);
                $amount = (float) $payload['amount'];
            }

            $present_reading = $payload['present_reading'] ?? null;
            $is_high_consumption = (int) ($payload['is_high_consumption'] ?? 0);

            $payor  = $payload['name'] ?? 'Starita Customer';
            $email  = $payload['email'] ?? null;
            $mobile = $payload['contact_no'] ?? null;
            ['payor' => $payor, 'email' => $email, 'contact_no' => $mobile] =
                $this->resolvePayorDetails($account_no, $payor, $email, $mobile);

            $existingBill = StaritaBill::where('reference_no', $reference_no)->first();
            if ($this->isStaritaBillPaid($existingBill)) {
                return $this->alreadyPaidHitpayResult($existingBill, $reference_no);
            }

            $payloadWithPayor = array_merge($payload, [
                'payor' => $payor,
                'customer' => ['name' => $payor, 'email' => $email],
            ]);

            $billData = [
                'account_no'      => $account_no,
                'payor'           => $payor,
                'email'           => $email,
                'contact_no'      => $mobile,
                'amount'          => $amount,
                'present_reading' => $present_reading,
                'is_high_consumption' => $is_high_consumption,
                'status'          => $existingBill?->status ?: 'pending',
                'payload'         => $payloadWithPayor,
                'initiated_at'    => $existingBill?->initiated_at ?? now(),
            ];
            StaritaBill::updateOrCreate(['reference_no' => $reference_no], $billData);

            Log::info('Starita bill saved during payment maintenance', [
                'reference_no' => $reference_no,
                'account_no' => $account_no,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'reference_no' => $reference_no,
                'account_no' => $account_no,
                'amount' => $amount,
                'payor' => $payor,
            ];
        } catch (\Throwable $e) {
            Log::error('Starita bill save failed during payment maintenance', [
                'error' => $e->getMessage(),
                'reference_no' => $payload['reference_no'] ?? null,
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * GET /api/novustream/starita/payment-request?rn=...&an=...&am=...&pr=...&hc=...
     * QR scan → save bill details → NovuPay checkout (HitPay disabled).
     *
     * Controlled by DUE_DATE_QR_PENALTY:
     *   true  — past due: apply penalty / amount_after_due, then save and open checkout
     *   false — past due: redirect to /payment-expired/{reference_no}
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
            'due_date_qr_penalty_mode' => $this->overdueQr->mode(),
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

        $existingBill = StaritaBill::where('reference_no', $rn)->first();
        if ($this->isStaritaBillPaid($existingBill)) {
            $confirmationUrl = $this->staritaConfirmationUrl($rn);
            Log::info('NovuStream Starita: bill already paid, redirecting to confirmation', [
                'reference_no' => $rn,
                'account_no' => $an,
                'url' => $confirmationUrl,
            ]);
            return redirect()->away($confirmationUrl);
        }

        $payload = [
            'reference_no'        => $rn,
            'account_no'          => $an,
            'amount'              => (float) $am,
            'present_reading'     => $request->pr,
            'is_high_consumption' => $request->hc ?? 0,
            'name'                => $request->name,
            'email'               => $request->email,
            'contact_no'          => $request->contact_no ?? $request->mobile ?? null,
            'due_date'            => $request->due_date ?? $request->dd ?? null,
            'amount_after_due'    => $request->amount_after_due ?? $request->aad ?? null,
            'penalty'             => $request->penalty ?? null,
            'utility'             => 'starita',
        ];

        $overdueEval = $this->overdueQr->evaluate($payload);
        if (!$overdueEval['continue']) {
            $referenceNo = $overdueEval['reference_no'] ?? $rn;
            $redirectUrl = $this->overdueQr->buildExpiredRedirectUrl(
                $referenceNo,
                $overdueEval['due_date'] ?? null,
                'starita'
            );

            Log::info('NovuStream Starita: overdue QR void — redirecting to expired page', [
                'reference_no' => $referenceNo,
                'url' => $redirectUrl,
                'mode' => $this->overdueQr->mode(),
            ]);

            return redirect()->away($redirectUrl);
        }

        $payload = $overdueEval['payload'];
        $overdueMeta = [
            'penalty_applied' => (bool) ($overdueEval['penalty_applied'] ?? false),
            'penalty_amount' => (float) ($overdueEval['penalty_amount'] ?? 0),
        ];

        Log::info('NovuStream Starita: saving bill before NovuPay checkout', [
            'reference_no' => $rn,
            'account_no'   => $an,
            'amount'       => (float) ($payload['amount'] ?? $am),
            'penalty_applied' => $overdueMeta['penalty_applied'],
            'penalty_amount' => $overdueMeta['penalty_amount'],
        ]);

        $result = $this->recordStaritaBill($payload);

        if (!empty($result['already_paid'])) {
            $confirmationUrl = $result['hitpay']['url'] ?? $this->staritaConfirmationUrl($rn);
            Log::info('NovuStream Starita: bill already paid, redirecting to confirmation', [
                'reference_no' => $rn,
                'url' => $confirmationUrl,
            ]);
            return redirect()->away($confirmationUrl);
        }

        if (!empty($result['error'])) {
            Log::warning('NovuStream Starita: failed to save bill before checkout', [
                'reference_no' => $rn,
                'error'        => $result['error'],
            ]);
            return response()->json($result, 500);
        }

        $checkoutUrl = route('payment.checkout.srwd', [
            'reference_no' => $rn,
        ]);

        Log::info('NovuStream Starita: redirecting to NovuPay checkout', [
            'reference_no' => $rn,
            'url' => $checkoutUrl,
        ]);

        return redirect()->away($checkoutUrl);
    }

    private function isStaritaBillPaid(?StaritaBill $bill): bool
    {
        if (!$bill) {
            return false;
        }

        return strtolower((string) $bill->status) === 'paid' || !is_null($bill->paid_at);
    }

    private function staritaConfirmationUrl(string $referenceNo): string
    {
        return rtrim((string) env('APP_URL'), '/') . '/payment-confirmation?ref=' . urlencode($referenceNo);
    }

    private function alreadyPaidHitpayResult(StaritaBill $bill, string $referenceNo): array
    {
        $txn = StaritaHitpayTransaction::where('reference_no', $referenceNo)
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->first();

        Log::info('Starita HitPay skipped: bill already paid', [
            'reference_no' => $referenceNo,
            'hitpay_id' => $bill->hitpay_reference ?? $txn?->hitpay_id,
        ]);

        return [
            'success' => true,
            'already_paid' => true,
            'hitpay' => [
                'id' => $bill->hitpay_reference ?? $txn?->hitpay_id,
                'url' => $this->staritaConfirmationUrl($referenceNo),
                'status' => 'paid',
            ],
            'transaction' => $txn,
        ];
    }

    private function findReusableHitpayTransaction(string $referenceNo, float $finalAmount): ?StaritaHitpayTransaction
    {
        $candidates = StaritaHitpayTransaction::where('reference_no', $referenceNo)
            ->whereNotNull('hitpay_id')
            ->whereIn('status', ['initiated', 'pending'])
            ->orderByDesc('id')
            ->get();

        foreach ($candidates as $txn) {
            $remote = $this->fetchHitpayPaymentRequest((string) $txn->hitpay_id);
            if (!$remote) {
                continue;
            }

            $status = strtolower((string) ($remote['status'] ?? ''));
            if (in_array($status, ['completed', 'succeeded', 'success', 'cancelled', 'canceled', 'expired', 'failed'], true)) {
                continue;
            }

            $remoteAmount = round((float) ($remote['amount'] ?? $txn->final_amount ?? 0), 2);
            if (abs($remoteAmount - round($finalAmount, 2)) >= 0.01) {
                continue;
            }

            $url = $remote['url'] ?? $txn->payment_url;
            if (empty($url)) {
                continue;
            }

            if ($url !== $txn->payment_url) {
                $txn->payment_url = $url;
                $txn->save();
            }

            return $txn;
        }

        return null;
    }

    private function fetchHitpayPaymentRequest(string $paymentRequestId): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
            ])->get(rtrim((string) env('HITPAY_API_URL'), '/') . "/payment-requests/{$paymentRequestId}");

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            Log::warning('Starita HitPay: failed to fetch existing request', [
                'hitpay_id' => $paymentRequestId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function placeholderCustomerEmail(): string
    {
        return 'srwdsystem2023@gmail.com';
    }

    private function isUsableCustomerEmail(?string $email): bool
    {
        $email = trim((string) $email);
        if ($email === '' || strcasecmp($email, $this->placeholderCustomerEmail()) === 0) {
            return false;
        }

        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Linked concessionaire email wins. The system mailbox is only a fallback
     * when the account has no email set up yet.
     */
    private function chooseCustomerEmail(?string $supplied, ?string $accountEmail): string
    {
        $accountEmail = trim((string) $accountEmail);
        if ($this->isUsableCustomerEmail($accountEmail)) {
            return $accountEmail;
        }

        $supplied = trim((string) $supplied);
        if ($this->isUsableCustomerEmail($supplied)) {
            return $supplied;
        }

        return $this->placeholderCustomerEmail();
    }

    private function resolvePayorDetails(string $accountNo, ?string $payor, ?string $email, ?string $contactNo): array
    {
        $resolved = [
            'payor' => $payor ?: 'Starita Customer',
            'email' => $this->chooseCustomerEmail($email, null),
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
            $resolved['email'] = $this->chooseCustomerEmail($email, $row->user_email ?? null);
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
