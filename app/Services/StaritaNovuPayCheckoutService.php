<?php

namespace App\Services;

use App\Models\StaritaBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StaritaNovuPayCheckoutService
{
    public function __construct(private NovuPayClient $client)
    {
    }

    /**
     * @return array{ok: bool, checkout_url?: string, error?: string, already_paid?: bool, voided?: bool, redirect?: string}
     */
    public function startForReference(string $referenceNo): array
    {
        $bill = $this->findBill($referenceNo);
        if (!$bill) {
            return ['ok' => false, 'error' => 'No Sta. Rita bill was found for this checkout.'];
        }

        if ($this->isPaid($bill)) {
            return [
                'ok' => false,
                'already_paid' => true,
                'redirect' => rtrim((string) env('APP_URL'), '/') . '/payment-confirmation?ref=' . urlencode($referenceNo),
            ];
        }

        if ($this->isVoidedPastDue($bill)) {
            return [
                'ok' => false,
                'voided' => true,
                'redirect' => route('payment.expired', ['reference_no' => $referenceNo]),
            ];
        }

        $amount = $this->amountDue($bill);
        if ($amount < $this->client->minAmount()) {
            return [
                'ok' => false,
                'error' => 'Amount must be at least PHP ' . number_format($this->client->minAmount(), 2) . '.',
            ];
        }

        $starita = StaritaBill::where('reference_no', $referenceNo)->first();
        $accountNo = (string) ($bill->account_no ?? $starita?->account_no ?? '');
        $email = $this->chooseCustomerEmail(
            $accountNo,
            $bill->account_email ?? null,
            $starita?->email
        );

        if ($starita && !empty($starita->novupay_uid) && !empty($starita->novupay_req_id)) {
            $storedAmount = round((float) ($starita->novupay_amount ?? 0), 2);
            if (abs($storedAmount - $amount) < 0.01) {
                $statusResult = $this->client->checkStatus((string) $starita->novupay_req_id);
                $remoteStatus = strtolower((string) data_get($statusResult, 'data.status', ''));
                if ($statusResult['ok'] && $remoteStatus === 'pending' && $this->checkoutEmailMatches($starita, $email)) {
                    return [
                        'ok' => true,
                        'checkout_url' => $this->client->buildCheckoutUrl((string) $starita->novupay_uid),
                    ];
                }
                if ($statusResult['ok'] && $remoteStatus === 'paid') {
                    return [
                        'ok' => false,
                        'already_paid' => true,
                        'redirect' => rtrim((string) env('APP_URL'), '/') . '/payment-confirmation?ref=' . urlencode($referenceNo),
                    ];
                }
            }
        }

        $payor = $bill->account_name ?? $starita?->payor ?? 'Sta. Rita Customer';
        $contact = $starita?->contact_no;
        $dueDate = $bill->due_date ?? null;

        Log::info('Starita NovuPay checkout using customer email', [
            'reference_no' => $referenceNo,
            'account_no' => $accountNo,
            'email' => $email,
        ]);

        $reqId = $this->client->makeReqId($referenceNo);
        $result = $this->client->createCheckout([
            'req_id' => $reqId,
            'amount' => $amount,
            'description' => 'Sta. Rita WD bill ' . $referenceNo . ($accountNo !== '' ? ' / Acct ' . $accountNo : ''),
            'email' => $email,
            'name' => $payor,
            'contact' => $contact,
            'expiry' => $this->client->expiryHours($dueDate ? (string) $dueDate : null),
            'param1' => $referenceNo,
            'param2' => $accountNo !== '' ? $accountNo : null,
            'notification_url' => $this->client->notificationUrl(),
            'redirect_url' => $this->client->redirectUrl() . '?req_id=' . rawurlencode($reqId),
        ]);

        if (!$result['ok']) {
            return [
                'ok' => false,
                'error' => $result['error'] ?? 'Unable to create NovuPay checkout.',
            ];
        }

        $data = $result['data'];
        $this->persistCheckout($referenceNo, $bill, $starita, $data, $amount, $payor, $email, $contact);

        return [
            'ok' => true,
            'checkout_url' => (string) $data['checkout_url'],
        ];
    }

    private function persistCheckout(
        string $referenceNo,
        object $bill,
        ?StaritaBill $starita,
        array $checkout,
        float $amount,
        string $payor,
        string $email,
        ?string $contact
    ): void {
        $payload = [
            'reference_no' => $referenceNo,
            'account_no' => $bill->account_no ?? null,
            'amount' => $amount,
            'due_date' => $bill->due_date ?? null,
            'novupay_req_id' => $checkout['req_id'] ?? null,
            'novupay_uid' => $checkout['uid'] ?? null,
            'checkout_email' => $email,
        ];

        $staritaSchema = Schema::connection('starita');
        $staritaData = [
            'account_no' => $bill->account_no ?? $starita?->account_no,
            'payor' => $payor,
            'email' => $email,
            'contact_no' => $contact,
            'amount' => $amount,
            'present_reading' => $bill->present_reading ?? $starita?->present_reading,
            'initiated_at' => now(),
            'status' => $starita?->status ?: 'pending',
            'payload' => array_merge(is_array($starita?->payload) ? $starita->payload : [], $payload),
        ];

        // Only write columns that exist on this DB (due_date is not present in production).
        if ($staritaSchema->hasColumn('starita_bills', 'due_date')) {
            $staritaData['due_date'] = $bill->due_date ?? $starita?->due_date;
        }
        if ($staritaSchema->hasColumn('starita_bills', 'novupay_id')) {
            $staritaData['novupay_id'] = $checkout['id'] ?? null;
        }
        if ($staritaSchema->hasColumn('starita_bills', 'novupay_uid')) {
            $staritaData['novupay_uid'] = $checkout['uid'] ?? null;
        }
        if ($staritaSchema->hasColumn('starita_bills', 'novupay_req_id')) {
            $staritaData['novupay_req_id'] = $checkout['req_id'] ?? null;
        }
        if ($staritaSchema->hasColumn('starita_bills', 'novupay_amount')) {
            $staritaData['novupay_amount'] = $checkout['amount'] ?? $amount;
        }
        if ($staritaSchema->hasColumn('starita_bills', 'novupay_status')) {
            $staritaData['novupay_status'] = 'pending';
        }

        StaritaBill::updateOrCreate(
            ['reference_no' => $referenceNo],
            $staritaData
        );

        if (!empty($bill->id) && empty($bill->from_novupay_only) && Schema::connection('starita_app')->hasTable('bill')) {
            try {
                $updates = [
                    'payment_method' => 'online',
                    'initiated_at' => now(),
                ];
                if (Schema::connection('starita_app')->hasColumn('bill', 'novupay_id')) {
                    $updates['novupay_id'] = $checkout['id'] ?? null;
                }
                if (Schema::connection('starita_app')->hasColumn('bill', 'novupay_uid')) {
                    $updates['novupay_uid'] = $checkout['uid'] ?? null;
                }
                if (Schema::connection('starita_app')->hasColumn('bill', 'novupay_req_id')) {
                    $updates['novupay_req_id'] = $checkout['req_id'] ?? null;
                }
                if (Schema::connection('starita_app')->hasColumn('bill', 'novupay_amount')) {
                    $updates['novupay_amount'] = $checkout['amount'] ?? $amount;
                }
                if (Schema::connection('starita_app')->hasColumn('bill', 'novupay_status')) {
                    $updates['novupay_status'] = 'pending';
                }

                DB::connection('starita_app')
                    ->table('bill')
                    ->where('id', $bill->id)
                    ->update($updates);
            } catch (\Throwable $e) {
                Log::warning('Unable to persist NovuPay checkout ids on starita_app.bill', [
                    'reference_no' => $referenceNo,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function findBill(string $referenceNo): ?object
    {
        try {
            $bill = DB::connection('starita_app')
                ->table('bill as b')
                ->leftJoin('readings as r', 'r.id', '=', 'b.reading_id')
                ->leftJoin('concessioner_accounts as ca', 'ca.account_no', '=', 'r.account_no')
                ->leftJoin('users as u', 'u.id', '=', 'ca.user_id')
                ->select([
                    'b.id',
                    'b.reference_no',
                    'b.total',
                    'b.amount',
                    'b.discount',
                    'b.penalty',
                    'b.amount_after_due',
                    'b.previous_unpaid',
                    'b.partial_payment',
                    'b.isPartial',
                    'b.amount_paid',
                    'b.isPaid',
                    'b.due_date',
                    'r.account_no',
                    'r.present_reading',
                    'u.name as account_name',
                    'u.email as account_email',
                ])
                ->where('b.reference_no', $referenceNo)
                ->first();

            if ($bill) {
                return $bill;
            }
        } catch (\Throwable $e) {
            Log::warning('starita_app bill lookup failed for NovuPay checkout', [
                'reference_no' => $referenceNo,
                'error' => $e->getMessage(),
            ]);
        }

        $starita = StaritaBill::where('reference_no', $referenceNo)->first();
        if (!$starita) {
            return null;
        }

        $payload = is_array($starita->payload) ? $starita->payload : [];

        return (object) [
            'id' => null,
            'from_novupay_only' => true,
            'reference_no' => $starita->reference_no,
            'total' => (float) $starita->amount,
            'amount' => (float) $starita->amount,
            'discount' => 0,
            'penalty' => (float) ($payload['penalty'] ?? 0),
            'amount_after_due' => (float) ($payload['amount_after_due'] ?? $starita->amount),
            'previous_unpaid' => 0,
            'partial_payment' => 0,
            'isPartial' => 0,
            'amount_paid' => 0,
            'isPaid' => strtolower((string) $starita->status) === 'paid' || !is_null($starita->paid_at),
            'due_date' => $payload['due_date'] ?? $starita->due_date,
            'account_no' => $starita->account_no,
            'present_reading' => $starita->present_reading,
            'account_name' => $starita->payor ?: ($payload['name'] ?? 'Sta. Rita Customer'),
            'account_email' => null,
        ];
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
     * Use the concessionaire account email when one is set up.
     * srwdsystem2023@gmail.com is only for accounts that have no email yet.
     */
    private function chooseCustomerEmail(string $accountNo, ?string $joinedEmail, ?string $storedEmail): string
    {
        $accountEmail = trim((string) $joinedEmail);
        if (!$this->isUsableCustomerEmail($accountEmail)) {
            $accountEmail = (string) ($this->lookupAccountEmail($accountNo) ?? '');
        }

        if ($this->isUsableCustomerEmail($accountEmail)) {
            return $accountEmail;
        }

        $storedEmail = trim((string) $storedEmail);
        if ($this->isUsableCustomerEmail($storedEmail)) {
            return $storedEmail;
        }

        return $this->placeholderCustomerEmail();
    }

    private function lookupAccountEmail(string $accountNo): ?string
    {
        if ($accountNo === '') {
            return null;
        }

        try {
            if (!Schema::connection('starita_app')->hasTable('concessioner_accounts')
                || !Schema::connection('starita_app')->hasTable('users')) {
                return null;
            }

            $email = DB::connection('starita_app')
                ->table('concessioner_accounts as ca')
                ->leftJoin('users as u', 'u.id', '=', 'ca.user_id')
                ->where('ca.account_no', $accountNo)
                ->value('u.email');

            $email = trim((string) $email);

            return $this->isUsableCustomerEmail($email) ? $email : null;
        } catch (\Throwable $e) {
            Log::warning('Starita NovuPay: account email lookup failed', [
                'account_no' => $accountNo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function checkoutEmailMatches(StaritaBill $starita, string $email): bool
    {
        $payload = is_array($starita->payload) ? $starita->payload : [];
        $previous = trim((string) ($payload['checkout_email'] ?? ''));
        if ($previous === '') {
            return false;
        }

        return strcasecmp($previous, $email) === 0;
    }

    private function isPaid(object $bill): bool
    {
        return !empty($bill->isPaid)
            || strtolower((string) ($bill->status ?? '')) === 'paid';
    }

    private function isVoidedPastDue(object $bill): bool
    {
        if (!$this->isPastDue($bill)) {
            return false;
        }

        return !$this->dueDateQrAllowsPenalty();
    }

    private function dueDateQrAllowsPenalty(): bool
    {
        $raw = config('novupay.due_date_qr_penalty');
        if ($raw === null || $raw === '') {
            return strtolower((string) config('novupay.overdue_qr_mode', 'void')) === 'penalty';
        }

        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
    }

    private function isPastDue(object $bill): bool
    {
        if (empty($bill->due_date)) {
            return false;
        }

        try {
            $due = Carbon::parse($bill->due_date)->timezone('Asia/Manila')->startOfDay();
        } catch (\Throwable $e) {
            return false;
        }

        return Carbon::today('Asia/Manila')->gt($due);
    }

    private function amountDue(object $bill): float
    {
        $discount = is_numeric($bill->discount ?? null) ? (float) $bill->discount : 0.0;
        $total = is_numeric($bill->total ?? null) ? (float) $bill->total : 0.0;
        $stored = is_numeric($bill->amount ?? null) ? (float) $bill->amount : 0.0;
        $penalty = (float) ($bill->penalty ?? 0);
        $afterDue = is_numeric($bill->amount_after_due ?? null) ? (float) $bill->amount_after_due : 0.0;

        $base = round(max($total - $discount, 0), 2);
        if ($base <= 0 && $stored > 0) {
            $base = round(max($stored - $discount, 0), 2);
            if ($penalty > 0 && ($afterDue <= 0 || abs($stored - $afterDue) < 0.01)) {
                $base = round(max($base - $penalty, 0), 2);
            }
        }

        $partial = 0.0;
        if (!empty($bill->isPartial)) {
            $partial = (float) ($bill->partial_payment ?: $bill->amount_paid ?: 0);
        }
        $grossBase = $base;
        $base = round(max($base - $partial, 0), 2);

        if (!$this->isPastDue($bill)) {
            return $base;
        }

        if ($penalty > 0) {
            $single = round($grossBase + $penalty, 2);
            $doubled = round($grossBase + ($penalty * 2), 2);
            if (abs($afterDue - $doubled) < 0.05 && abs($stored - $single) < 0.05) {
                return round(max($single - $partial, 0), 2);
            }
        }

        if ($afterDue > $base + 0.001) {
            return round(max($afterDue - $partial, 0), 2);
        }

        if ($penalty > 0) {
            return round($base + $penalty, 2);
        }

        return round($base * 1.10, 2);
    }
}
