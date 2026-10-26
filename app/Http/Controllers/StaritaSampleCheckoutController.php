<?php

namespace App\Http\Controllers;

use App\Models\StaritaBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StaritaSampleCheckoutController extends Controller
{
    /**
     * Checkout for a Sta. Rita bill. Does not create a live payment while HitPay is disabled.
     */
    public function show(?string $reference_no = null)
    {
        $isSample = request()->routeIs('payment.checkout.sample.srwd');
        $bill = $this->findBill($reference_no);
        if (!$bill) {
            abort(404, 'No Sta. Rita bill was found for this checkout.');
        }

        $lines = collect();
        if (!empty($bill->id) && empty($bill->from_novupay_only)) {
            $lines = DB::connection('starita_app')
                ->table('bill_breakdown')
                ->where('bill_id', $bill->id)
                ->orderBy('id')
                ->get(['name', 'amount']);
        }

        $amountDue = $this->amountDue($bill);
        $pastDue = $this->isPastDue($bill);
        $novupayFee = 10.0;
        $qrphRail = round($amountDue <= 2000 ? 20 : ($amountDue * 0.01), 1);
        $gcashRail = round($amountDue * 0.023, 1);
        $defaultMethod = $amountDue < 800 ? 'gcash' : 'qrph';

        $sampleQr = QrCode::size(168)->margin(1)->generate(
            'NOVUPAY CHECKOUT - NOT A LIVE PAYMENT - ' . ($bill->reference_no ?? '')
        );

        return view('payment.starita-checkout-sample', [
            'bill' => $bill,
            'lines' => $lines,
            'amountDue' => $amountDue,
            'pastDue' => $pastDue,
            'defaultMethod' => $defaultMethod,
            'isSample' => $isSample,
            'fees' => [
                'novupay' => $novupayFee,
                'qrph' => round($qrphRail + $novupayFee, 2),
                'gcash' => round($gcashRail + $novupayFee, 2),
            ],
            'sampleQr' => $sampleQr,
        ]);
    }

    private function findBill(?string $referenceNo): ?object
    {
        $query = DB::connection('starita_app')
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
                'b.bill_period_from',
                'b.bill_period_to',
                'b.created_at',
                'r.account_no',
                'r.previous_reading',
                'r.present_reading',
                'r.consumption',
                'ca.address',
                'ca.meter_serial_no',
                'u.name as account_name',
            ]);

        if ($referenceNo) {
            $bill = $query->where('b.reference_no', $referenceNo)->first();
            if ($bill) {
                return $bill;
            }

            return $this->findNovupayBill($referenceNo);
        }

        return $query
            ->where('b.isPaid', 0)
            ->whereNotNull('b.reference_no')
            ->where('b.amount', '>', 0)
            ->orderByDesc('b.id')
            ->first();
    }

    private function findNovupayBill(string $referenceNo): ?object
    {
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
            'bill_period_from' => null,
            'bill_period_to' => null,
            'created_at' => $starita->created_at,
            'account_no' => $starita->account_no,
            'previous_reading' => null,
            'present_reading' => $starita->present_reading,
            'consumption' => null,
            'address' => null,
            'meter_serial_no' => null,
            'account_name' => $starita->payor ?: ($payload['name'] ?? 'Sta. Rita Customer'),
        ];
    }

    private function amountDue(object $bill): float
    {
        $discount = is_numeric($bill->discount) ? (float) $bill->discount : 0.0;
        $total = is_numeric($bill->total) ? (float) $bill->total : 0.0;
        $stored = is_numeric($bill->amount) ? (float) $bill->amount : 0.0;
        $penalty = (float) ($bill->penalty ?? 0);
        $afterDue = is_numeric($bill->amount_after_due) ? (float) $bill->amount_after_due : 0.0;

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
        $base = round(max($base - $partial, 0), 2);

        if (!$this->isPastDue($bill)) {
            return $base;
        }

        if ($afterDue > $base + 0.001) {
            return round(max($afterDue - $partial, 0), 2);
        }

        if ($penalty > 0) {
            return round($base + $penalty, 2);
        }

        return round($base * 1.10, 2);
    }

    private function isPastDue(object $bill): bool
    {
        if (empty($bill->due_date)) {
            return false;
        }

        $due = Carbon::parse($bill->due_date)->timezone('Asia/Manila')->endOfDay();

        return Carbon::now('Asia/Manila')->gt($due);
    }
}
