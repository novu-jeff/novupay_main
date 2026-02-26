<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Models\Bill;
use App\Models\KaelcoBill;
use App\Models\StaritaBill;
use App\Models\MorongBill;
use App\Models\StaritaHitpayTransaction;

class PaymentWebhookController extends Controller
{
    /**
     * 🔹 Handle HitPay Webhook (REAL SOURCE OF TRUTH)
     */
    public function handleWebhook(Request $request)
    {
        Log::info('HitPay Webhook RAW', [
            'raw'  => $request->getContent(),
            'post' => $request->post()
        ]);

        $data = $request->post();
        if (!$data) {
            Log::warning('HitPay Webhook: no data received');
            return response()->json(['error' => 'No data received'], 400);
        }

        // Reference number may appear in different fields
        $reference_no = $data['reference_number']
            ?? $data['payment_request_id']
            ?? $data['reference']
            ?? null;

        if (!$reference_no) {
            Log::warning('HitPay Webhook: missing reference_number', $data);
            return response()->json(['error' => 'Missing reference_number'], 400);
        }

        // Detect utility and fetch correct bill table
        $utility = $this->detectUtility($reference_no);

        if (in_array($utility, ['srwd', 'morong'], true)) {
            Log::info('NovuStream webhook received', [
                'merchant'     => $utility === 'srwd' ? 'starita' : 'morong',
                'utility'      => $utility,
                'reference_no' => $reference_no,
                'status'       => $data['status'] ?? null,
                'payment_id'   => $data['payment_id'] ?? $data['id'] ?? null,
            ]);
        }

        switch ($utility) {
            case 'kaelco':
                $bill = KaelcoBill::where('reference_no', $reference_no)->first();
                break;

            case 'srwd':
                $bill = StaritaBill::where('reference_no', $reference_no)->first();
                break;

            case 'morong':
                $bill = MorongBill::where('reference_no', $reference_no)->first();
                break;

            default: // pelco
                $bill = Bill::where('reference_no', $reference_no)->first();
        }

        if (!$bill) {
            Log::channel('sync')->warning('Webhook Bill NOT FOUND', ['reference_no' => $reference_no]);
            Log::warning('Webhook Bill NOT FOUND', [
                'reference_no' => $reference_no,
                'utility'       => $utility,
                'novustream'    => in_array($utility, ['srwd', 'morong'], true),
            ]);
            return response()->json(['error' => 'Bill not found'], 404);
        }

        $status = strtolower($data['status'] ?? '');

        // Status mapping
        $mappedStatus = match ($status) {
            'completed', 'succeeded' => 'paid',
            'failed'                 => 'failed',
            'cancelled', 'canceled'  => 'cancelled',
            default                  => 'pending',
        };

        // Update DB from webhook
        $resolvedHitpayRef = $bill->hitpay_reference
            ?? $data['payment_id']
            ?? $data['id']
            ?? null;

        $updateData = [
            'status'  => $mappedStatus,
            'paid_at' => $mappedStatus === 'paid' ? now() : null,
            'payload' => $data,
        ];
        if (method_exists($bill, 'getConnectionName')) {
            $connection = $bill->getConnectionName() ?: config('database.default');
            if (Schema::connection($connection)->hasColumn($bill->getTable(), 'hitpay_reference')) {
                $updateData['hitpay_reference'] = $resolvedHitpayRef;
            }
        }

        $bill->update($updateData);

        Log::info('Bill updated via webhook', [
            'utility'      => $utility,
            'reference_no' => $bill->reference_no,
            'status'       => $mappedStatus,
            'novustream'   => in_array($utility, ['srwd', 'morong'], true),
        ]);

        if ($utility === 'srwd') {
            StaritaHitpayTransaction::where('reference_no', $bill->reference_no)->update([
                'status' => $mappedStatus,
                'paid_at' => $mappedStatus === 'paid' ? now() : null,
            ]);
        }

        if ($utility === 'srwd' && $mappedStatus === 'paid') {
            Log::info('starita:sync-paid-status source: Starita paid status updated in novupay', [
                'reference_no' => $bill->reference_no,
                'paid_at'      => $bill->paid_at?->toIso8601String(),
            ]);
        }

        if (in_array($utility, ['srwd', 'morong'], true)) {
            Log::info('NovuStream webhook processed', [
                'merchant'     => $utility === 'srwd' ? 'starita' : 'morong',
                'reference_no' => $bill->reference_no,
                'action'       => 'bill_updated',
                'new_status'   => $mappedStatus,
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * 🔹 Handle Redirect — ALWAYS TRUST DATABASE, NOT HitPay redirect
     * Pending = treat as PAID
     */
    public function handleRedirect(Request $request)
    {
        Log::info('HitPay redirect received', [
            'query' => $request->query(),
            'input' => $request->all(),
        ]);

        $hitpayRef = $request->query('reference') ?? $request->input('reference');

        if (!$hitpayRef) {
            Log::warning('HitPay redirect: missing reference');
            return redirect()->away(env('APP_URL') . '/payment-error');
        }

        // Try all models (include Morong for NovuStream)
        $bill = Bill::where('hitpay_reference', $hitpayRef)->first()
            ?? KaelcoBill::where('hitpay_reference', $hitpayRef)->first()
            ?? StaritaBill::where('hitpay_reference', $hitpayRef)->first()
            ?? MorongBill::where('hitpay_reference', $hitpayRef)->first();

        if (!$bill) {
            Log::warning('HitPay redirect: bill not found', ['hitpay_reference' => $hitpayRef]);
            return redirect()->away(env('APP_URL') . '/payment-error');
        }

        $billClass = get_class($bill);
        $isNovuStream = $billClass === StaritaBill::class || $billClass === MorongBill::class;
        if ($isNovuStream) {
            Log::info('NovuStream redirect: bill found', [
                'merchant'        => $billClass === StaritaBill::class ? 'starita' : 'morong',
                'reference_no'    => $bill->reference_no,
                'hitpay_reference' => $hitpayRef,
            ]);
        }

        /**
         * Wait for webhook to update the DB.
         * Webhook comes a few ms AFTER redirect.
         */
        for ($i = 0; $i < 5; $i++) {
            $bill->refresh();

            if (!in_array($bill->status, ['pending', 'initiated'])) {
                break;
            }

            usleep(200000); // 0.2 seconds
        }

        /**
         * ⭐ PENDING = TREAT AS SUCCESS ⭐
         */
        if (in_array($bill->status, ['paid', 'pending'])) {
            $finalStatus = 'paid';
        } elseif (in_array($bill->status, ['failed', 'cancelled'])) {
            $finalStatus = $bill->status;
        } else {
            $finalStatus = 'pending';
        }

        $redirectUrl = match ($finalStatus) {
            'paid' =>
                env('APP_URL') . "/payment-confirmation?ref={$bill->reference_no}",

            'failed', 'cancelled' =>
                env('APP_URL') . "/payment-failed?ref={$bill->reference_no}",

            default =>
                env('APP_URL') . "/payment-confirmation?ref={$bill->reference_no}",
        };

        Log::info('Redirect final result', [
            'reference_no'  => $bill->reference_no,
            'final_status'  => $finalStatus,
            'redirect'      => $redirectUrl,
            'novustream'    => $isNovuStream ?? false,
        ]);

        if ($isNovuStream ?? false) {
            Log::info('NovuStream redirect completed', [
                'merchant'     => $billClass === StaritaBill::class ? 'starita' : 'morong',
                'reference_no' => $bill->reference_no,
                'action'       => 'redirect_user',
                'final_status' => $finalStatus,
                'redirect_to'  => $redirectUrl,
            ]);
        }

        return redirect()->away($redirectUrl);
    }


    /**
     * Detect utility by reference number
     */
    private function detectUtility($reference)
    {
        $ref = strtoupper($reference);

        if (str_contains($ref, 'KAELCO') || str_starts_with($ref, 'NSR-KAELCO')) {
            return 'kaelco';
        }

        if (str_contains($ref, 'SRWD') || str_contains($ref, 'STARITA')) {
            return 'srwd';
        }

        if (str_contains($ref, 'MRWD') || str_contains($ref, 'MORONG') || str_starts_with($ref, 'NST-MRWD')) {
            return 'morong';
        }

        return 'pelco';
    }
}

