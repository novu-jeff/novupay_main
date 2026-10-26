<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OverdueQrService
{
    public function mode(): string
    {
        $bool = config('novupay.due_date_qr_penalty');
        if ($bool !== null && $bool !== '') {
            return filter_var($bool, FILTER_VALIDATE_BOOLEAN) ? 'penalty' : 'void';
        }

        $mode = strtolower((string) config('novupay.overdue_qr_mode', 'void'));

        return in_array($mode, ['void', 'penalty'], true) ? $mode : 'void';
    }

    public function isVoidMode(): bool
    {
        return $this->mode() === 'void';
    }

    public function isPenaltyMode(): bool
    {
        return $this->mode() === 'penalty';
    }

    public function resolveDueDate(array $payload): ?Carbon
    {
        if (!empty($payload['due_date'])) {
            try {
                return Carbon::parse($payload['due_date'])->timezone('Asia/Manila');
            } catch (\Throwable $e) {
                Log::warning('OverdueQr: invalid due_date in payload', [
                    'reference_no' => $payload['reference_no'] ?? null,
                    'due_date' => $payload['due_date'],
                ]);
            }
        }

        $referenceNo = trim((string) ($payload['reference_no'] ?? ''));
        if ($referenceNo === '') {
            return null;
        }

        try {
            if (!Schema::connection('starita_app')->hasTable('bill')) {
                return null;
            }

            $row = DB::connection('starita_app')
                ->table('bill')
                ->where('reference_no', $referenceNo)
                ->value('due_date');

            if (!empty($row)) {
                return Carbon::parse($row)->timezone('Asia/Manila');
            }
        } catch (\Throwable $e) {
            Log::warning('OverdueQr: starita_app bill due_date lookup failed', [
                'reference_no' => $referenceNo,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function isPastDue(array $payload): bool
    {
        $dueDate = $this->resolveDueDate($payload);
        if (!$dueDate) {
            return false;
        }

        // Match SOA rule: penalty starts the day AFTER due date (penalty_date).
        // Due date itself remains payable without penalty.
        $today = Carbon::today('Asia/Manila');

        return $today->gt($dueDate->copy()->startOfDay());
    }

    public function resolvePayableAmount(array $payload): float
    {
        $base = (float) ($payload['amount'] ?? 0);

        if (isset($payload['amount_after_due']) && is_numeric($payload['amount_after_due'])) {
            $afterDue = round((float) $payload['amount_after_due'], 2);
            if ($afterDue > $base + 0.001) {
                return $afterDue;
            }
        }

        if (isset($payload['penalty']) && is_numeric($payload['penalty'])) {
            return round($base + (float) $payload['penalty'], 2);
        }

        $referenceNo = trim((string) ($payload['reference_no'] ?? ''));
        if ($referenceNo !== '') {
            $fromDb = $this->resolvePayableAmountFromBillTable($referenceNo, $base);
            if ($fromDb !== null) {
                return $fromDb;
            }
        }

        Log::warning('OverdueQr: penalty mode without penalty fields in payload or DB', [
            'reference_no' => $referenceNo,
            'amount' => $base,
        ]);

        // Starita SOA rule: 10% of amount due when past due and no explicit penalty fields.
        $utility = strtolower((string) ($payload['utility'] ?? $payload['consumer_type'] ?? ''));
        if ($utility === 'starita' || $utility === 'srwd' || str_starts_with($referenceNo, 'NST-SRWD-')) {
            return round($base * 1.10, 2);
        }

        return round($base, 2);
    }

    private function resolvePayableAmountFromBillTable(string $referenceNo, float $fallbackBase): ?float
    {
        try {
            if (!Schema::connection('starita_app')->hasTable('bill')) {
                return null;
            }

            $columns = Schema::connection('starita_app')->getColumnListing('bill');
            $select = array_values(array_intersect(
                ['amount_after_due', 'penalty', 'amount'],
                $columns
            ));

            if ($select === []) {
                return null;
            }

            $row = DB::connection('starita_app')
                ->table('bill')
                ->where('reference_no', $referenceNo)
                ->first($select);

            if (!$row) {
                return null;
            }

            $amount = (float) ($row->amount ?? $fallbackBase);
            $penalty = (float) ($row->penalty ?? 0);

            // Prefer amount_after_due only when it actually includes the surcharge.
            if (isset($row->amount_after_due) && is_numeric($row->amount_after_due)) {
                $afterDue = round((float) $row->amount_after_due, 2);
                if ($afterDue > $amount + 0.001) {
                    return $afterDue;
                }
            }

            if ($penalty > 0) {
                return round($amount + $penalty, 2);
            }
        } catch (\Throwable $e) {
            Log::warning('OverdueQr: starita_app payable amount lookup failed', [
                'reference_no' => $referenceNo,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public function buildExpiredRedirectUrl(string $referenceNo, ?Carbon $dueDate = null, ?string $utility = null): string
    {
        $override = trim((string) config('novupay.overdue_redirect_url', ''));
        $query = [];

        if ($dueDate) {
            $query['expires_at'] = $dueDate->copy()->timezone('Asia/Manila')->endOfDay()->format('Y-m-d H:i:s');
        }
        if ($utility) {
            $query['utility'] = $utility;
        }

        if ($override !== '') {
            $url = str_replace('{reference_no}', rawurlencode($referenceNo), $override);
            if ($query !== []) {
                $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
            }

            return $url;
        }

        $url = route('payment.expired', ['reference_no' => $referenceNo]);

        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    /**
     * Starita bill.amount is historically stored as amount-after-due (includes penalty),
     * while SOA "Amount Due" uses bill.total. Mobile QR often encodes am=amount (with penalty)
     * even before the due date. Normalize from starita_app.bill when available.
     *
     * Before due  → amount = total - discount (no penalty)
     * On/after due → amount_after_due / amount+penalty kept for evaluate()
     */
    public function normalizeStaritaPayload(array $payload): array
    {
        $referenceNo = trim((string) ($payload['reference_no'] ?? ''));
        if ($referenceNo === '') {
            return $payload;
        }

        $row = $this->fetchStaritaBillRow($referenceNo);
        if (!$row) {
            Log::warning('OverdueQr: starita bill row missing; QR amount may include penalty', [
                'reference_no' => $referenceNo,
                'qr_amount' => $payload['amount'] ?? null,
            ]);
            return $this->stripPenaltyIfBeforeDue($payload);
        }

        $qrAmount = (float) ($payload['amount'] ?? 0);
        $computed = self::computeStaritaAmounts(
            (float) ($row->total ?? 0),
            (float) ($row->discount ?? 0),
            (float) ($row->amount ?? 0),
            (float) ($row->amount_after_due ?? 0),
            (float) ($row->penalty ?? 0)
        );
        $amountDue = $computed['amount_due'];
        $amountAfterDue = $computed['amount_after_due'];
        $penalty = (float) ($row->penalty ?? 0);

        if (!empty($row->due_date) && empty($payload['due_date'])) {
            try {
                $payload['due_date'] = Carbon::parse($row->due_date)->timezone('Asia/Manila')->toDateString();
            } catch (\Throwable $e) {
                // keep payload due_date unset
            }
        }

        $payload['penalty'] = $penalty;
        $payload['amount_after_due'] = $amountAfterDue;

        // Always expose pre-due amount as `amount`. evaluate() adds penalty only when past due.
        if (abs($qrAmount - $amountDue) > 0.009) {
            Log::info('OverdueQr: correcting Starita QR amount from bill.total', [
                'reference_no' => $referenceNo,
                'qr_amount' => $qrAmount,
                'amount_due' => $amountDue,
                'amount_after_due' => $amountAfterDue,
                'penalty' => $penalty,
                'due_date' => $payload['due_date'] ?? null,
                'past_due' => $this->isPastDue($payload),
            ]);
        }

        $payload['amount'] = $amountDue;

        return $payload;
    }

    /**
     * @return array{amount_due: float, amount_after_due: float}
     */
    public static function computeStaritaAmounts(
        float $total,
        float $discount,
        float $storedAmount,
        float $storedAfterDue,
        float $penalty
    ): array {
        $amountDue = round(max($total - $discount, 0), 2);
        if ($amountDue <= 0 && $storedAmount > 0) {
            $amountDue = $penalty > 0 && abs($storedAmount - $storedAfterDue) < 0.01
                ? round(max($storedAmount - $penalty, 0), 2)
                : round($storedAmount, 2);
        }

        $singleAfterDue = $penalty > 0 ? round($amountDue + $penalty, 2) : $amountDue;
        $doubledAfterDue = $penalty > 0 ? round($amountDue + ($penalty * 2), 2) : $amountDue;

        // amount is the single after-due figure; amount_after_due was saved with the penalty added twice.
        if (
            $penalty > 0
            && abs($storedAmount - $singleAfterDue) < 0.05
            && abs($storedAfterDue - $doubledAfterDue) < 0.05
        ) {
            $amountAfterDue = $singleAfterDue;
        } else {
            $amountAfterDue = $storedAfterDue;
            if ($amountAfterDue <= $amountDue + 0.001 && $penalty > 0) {
                $amountAfterDue = $singleAfterDue;
            } elseif ($amountAfterDue <= 0) {
                $amountAfterDue = $singleAfterDue;
            }
        }

        return [
            'amount_due' => $amountDue,
            'amount_after_due' => round($amountAfterDue, 2),
        ];
    }

    public static function checkoutAmount(float $amountDue, float $amountAfterDue, bool $pastDue): float
    {
        if ($pastDue && $amountAfterDue > $amountDue + 0.001) {
            return round($amountAfterDue, 2);
        }

        return round($amountDue, 2);
    }

    /**
     * When still within due date, never charge amount_after_due / +penalty.
     */
    public function stripPenaltyIfBeforeDue(array $payload): array
    {
        if ($this->isPastDue($payload)) {
            return $payload;
        }

        $amount = round((float) ($payload['amount'] ?? 0), 2);
        $penalty = (float) ($payload['penalty'] ?? 0);
        $afterDue = isset($payload['amount_after_due']) && is_numeric($payload['amount_after_due'])
            ? round((float) $payload['amount_after_due'], 2)
            : 0.0;

        if ($penalty > 0 && $afterDue > 0 && abs($amount - $afterDue) < 0.02) {
            $payload['amount'] = round(max($amount - $penalty, 0), 2);
        }

        return $payload;
    }

    private function fetchStaritaBillRow(string $referenceNo): ?object
    {
        try {
            if (!Schema::connection('starita_app')->hasTable('bill')) {
                return null;
            }

            $columns = Schema::connection('starita_app')->getColumnListing('bill');
            $select = array_values(array_intersect(
                ['reference_no', 'total', 'amount', 'amount_after_due', 'penalty', 'discount', 'due_date'],
                $columns
            ));

            if ($select === []) {
                return null;
            }

            return DB::connection('starita_app')
                ->table('bill')
                ->where('reference_no', $referenceNo)
                ->first($select);
        } catch (\Throwable $e) {
            Log::warning('OverdueQr: starita bill lookup failed', [
                'reference_no' => $referenceNo,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{
     *   continue: bool,
     *   payload?: array,
     *   void?: bool,
     *   reference_no?: string,
     *   due_date?: ?Carbon,
     *   penalty_applied?: bool,
     *   penalty_amount?: float
     * }
     */
    public function evaluate(array $payload): array
    {
        $utility = strtolower((string) ($payload['utility'] ?? $payload['consumer_type'] ?? ''));
        if ($utility === 'starita' || $utility === 'srwd' || str_starts_with((string) ($payload['reference_no'] ?? ''), 'NST-SRWD-')) {
            $payload = $this->normalizeStaritaPayload($payload);
        }

        $dueDate = $this->resolveDueDate($payload);
        if ($dueDate && empty($payload['due_date'])) {
            $payload['due_date'] = $dueDate->toDateString();
        }

        if (!$this->isPastDue($payload)) {
            $payload = $this->stripPenaltyIfBeforeDue($payload);
            return ['continue' => true, 'payload' => $payload];
        }

        $referenceNo = trim((string) ($payload['reference_no'] ?? ''));
        $utility = $payload['utility'] ?? ($payload['consumer_type'] ?? null);

        if ($this->isVoidMode()) {
            Log::info('OverdueQr: blocking payment (void mode)', [
                'reference_no' => $referenceNo,
                'due_date' => $dueDate?->toDateString(),
                'mode' => $this->mode(),
            ]);

            return [
                'continue' => false,
                'void' => true,
                'reference_no' => $referenceNo,
                'due_date' => $dueDate,
                'utility' => $utility,
            ];
        }

        // amount is pre-due (from normalize); prefer amount_after_due / penalty fields.
        $baseAmount = (float) ($payload['amount'] ?? 0);
        $payable = $this->resolvePayableAmount($payload);
        $payload['amount'] = $payable;
        $payload['amount_after_due'] = $payable;
        $penaltyApplied = $payable > $baseAmount + 0.001;

        Log::info('OverdueQr: penalty mode — using overdue amount', [
            'reference_no' => $referenceNo,
            'base_amount' => $baseAmount,
            'payable_amount' => $payable,
            'penalty_applied' => $penaltyApplied,
            'mode' => $this->mode(),
        ]);

        return [
            'continue' => true,
            'payload' => $payload,
            'penalty_applied' => $penaltyApplied,
            'penalty_amount' => $penaltyApplied ? round($payable - $baseAmount, 2) : 0.0,
        ];
    }
}
