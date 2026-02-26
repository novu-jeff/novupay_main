<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Bill;
use App\Models\KaelcoBill;
use App\Models\StaritaBill;
use App\Models\MorongBill;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;  
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\PaymentWebhookController;

class PaymentPageController extends Controller
{
    public function confirmation(Request $request)
    {
        $reference = $request->query('ref');

        // dd($reference);

        // Determine which utility this reference belongs to
        $utility = $this->detectUtility($reference);

        // dd($utility);

        switch ($utility) {
            case 'kaelco':
                $bill = KaelcoBill::where('reference_no', $reference)->first();
                break;

            case 'pelco':
                $bill = Bill::where('reference_no', $reference)->first();
                break;

            case 'srwd':
                $bill = StaritaBill::where('reference_no', $reference)->first();
                break;

            case 'morong':
                $bill = MorongBill::where('reference_no', $reference)->first();
                break;

            default:
                $bill = null;
        }

        // dd($bill);
        if (!$bill) {
            Log::warning('Payment success page: Bill not found', ['ref' => $reference]);

            return view('payment.paid', [
                'payload' => [
                    'title' => 'Payment Not Found',
                    'message' => 'Sorry, we could not find your payment record.',
                    'status' => 'failed',
                    'amount' => 0,
                    'reference_no' => $reference,
                ],
            ]);
        }

        // Extract bill months paid
        $decoded = json_decode($bill->bill_month ?? '[]', true);

        $payments = $decoded['payments'] ?? [];   // Extract real list of payments
        $billMonths = $this->extractBillMonthsFromPayments($payments);

        // dd($payments, $billMonths);
        $payload = [
            'title' => match (strtolower($bill->status)) {
                'paid' => 'Payment Successful!',
                'pending' => 'Payment Pending',
                'failed' => 'Payment Failed',
                default => 'Payment Status',
            },
            'message' => match (strtolower($bill->status)) {
                'paid' => 'Thank you! Your payment has been successfully processed.',
                'pending' => 'Your payment is still being processed. Please check back shortly.',
                'failed' => 'Unfortunately, your payment was not completed.',
                'cancelled' => 'Your payment has been cancelled.',
                default => '',
            },

            'status' => $bill->status,
            'reference_no' => $bill->reference_no,
            'amount' => number_format($bill->amount, 2),

            'date_paid' => $bill->paid_at
                ? Carbon::parse($bill->paid_at)->format('M d, Y h:i A')
                : Carbon::now()->format('M d, Y h:i A'),

            'expires_at' => $bill->due_date
                ? Carbon::parse($bill->due_date)->format('Y-m-d H:i:s')
                : null,

            'payment_id' => $bill->hitpay_reference ?? '-',

            // ⭐ bill months paid (multiple supported) ⭐
            'bill_month' => $billMonths,
        ];

        // Kaelco: breakdown for receipt (Amount + Disconnection Fee + Surcharge = Total)
        if ($utility === 'kaelco') {
            $amountDue = (float) $bill->amount;
            $disconnectionFee = (float) ($bill->disconnection_fee ?? 0);
            $surcharge = (float) ($bill->surcharge ?? 0);
            $totalAmount = $amountDue + $disconnectionFee + $surcharge;
            $payload['show_breakdown'] = true;
            $payload['amount_due'] = number_format($amountDue, 2);
            $payload['disconnection_fee'] = number_format($disconnectionFee, 2);
            $payload['surcharge'] = number_format($surcharge, 2);
            $payload['amount'] = number_format($totalAmount, 2);
        }

        Log::info('Rendering payment success page', [
            'reference_no' => $bill->reference_no,
            'status' => $bill->status,
            'utility' => $utility,
        ]);
        if ($utility === 'kaelco') {
            $this->callKaelcoWebhook($bill);
        }


        return view('payment.paid', compact('payload'));
    }

    /**
     * Payment Failed Page
     */
    public function failed(Request $request)
    {
        $reference = $request->query('ref');

        $utility = $this->detectUtility($reference);

        switch ($utility) {
            case 'kaelco':
                $bill = KaelcoBill::where('reference_no', $reference)->first();
                break;

            case 'pelco':
                $bill = Bill::where('reference_no', $reference)->first();
                break;

            case 'srwd':
                $bill = StaritaBill::where('reference_no', $reference)->first();
                break;

            case 'morong':
                $bill = MorongBill::where('reference_no', $reference)->first();
                break;

            default:
                $bill = null;
        }

        if (!$bill) {
            return view('payment.paid', [
                'payload' => [
                    'title' => 'Payment Failed',
                    'message' => 'We could not find your payment details.',
                    'status' => 'failed',
                    'amount' => 0,
                    'reference_no' => $reference,
                ],
            ]);
        }

        $payload = [
            'title' => 'Payment Failed',
            'message' => 'Your payment could not be processed. Please try again later.',
            'status' => $bill->status ?? 'failed',
            'reference_no' => $bill->reference_no,
            'amount' => number_format($bill->amount, 2),
            'date_paid' => Carbon::now()->format('M d, Y h:i A'),
            'payment_id' => $bill->hitpay_reference ?? '-',
        ];

        return view('payment.paid', compact('payload'));
    }

    /**
     * Payment Pending Page
     */
    public function pending(Request $request)
    {
        $reference = $request->query('ref');

        $utility = $this->detectUtility($reference);

        switch ($utility) {
            case 'kaelco':
                $bill = KaelcoBill::where('reference_no', $reference)->first();
                break;

            case 'pelco':
                $bill = Bill::where('reference_no', $reference)->first();
                break;

            case 'srwd':
                $bill = StaritaBill::where('reference_no', $reference)->first();
                break;

            case 'morong':
                $bill = MorongBill::where('reference_no', $reference)->first();
                break;

            default:
                $bill = null;
        }

        if (!$bill) {
            return view('payment.paid', [
                'payload' => [
                    'title' => 'Payment Not Found',
                    'message' => 'We could not find your payment information.',
                    'status' => 'pending',
                    'reference_no' => $reference,
                    'amount' => 0,
                ],
            ]);
        }

        $payload = [
            'title'         => 'Payment Pending',
            'message'       => 'Your payment is still being processed.',
            'status'        => 'pending',
            'reference_no'  => $bill->reference_no,
            'amount'        => number_format($bill->amount, 2),
            'date_paid'     => now()->format('M d, Y h:i A'),
            'payment_id'    => $bill->hitpay_reference ?? '-',
            'expires_at'    => $bill->due_date ?? null,
        ];

        return view('payment.paid', compact('payload'));
    }

    /**
     * PDF Download
     */
    public function downloadPdf($id)
    {
        // Search in all utilities
        $bill = Bill::where('hitpay_reference', $id)->first()
            ?? KaelcoBill::where('hitpay_reference', $id)->first()
            ?? StaritaBill::where('hitpay_reference', $id)->first()
            ?? MorongBill::where('hitpay_reference', $id)->first();

        if (!$bill) {
            abort(404, 'Payment not found');
        }
        // dd($bill);

        $payloadData = $bill->payload ?? [];
        if (is_string($payloadData)) {
            $payloadData = json_decode($payloadData, true) ?? [];
        }
        if (!is_array($payloadData)) {
            $payloadData = [];
        }

        $normalizeText = static function ($value) {
            if (is_array($value)) {
                $value = implode(', ', array_filter(array_map('strval', $value)));
            } elseif (is_object($value)) {
                $value = json_encode($value);
            }
            if ($value === null) {
                return '';
            }
            return is_string($value) ? $value : (string) $value;
        };

        $description = $normalizeText(
            $bill->description
                ?? $payloadData['purpose']
                ?? $payloadData['description']
                ?? ''
        );
        $purpose = $normalizeText(
            $payloadData['purpose']
                ?? $payloadData['description']
                ?? $description
        );
        $paymentMethod = $normalizeText(
            $payloadData['payment_method']
                ?? $payloadData['payment_type']
                ?? $payloadData['method']
                ?? $payloadData['channel']
                ?? ''
        );
        $paidAt = $bill->paid_at
            ?? ($payloadData['paid_at'] ?? $payloadData['completed_at'] ?? null);
        $paidAtValue = $paidAt
            ? Carbon::parse($paidAt)->format('M d, Y h:i A')
            : now()->format('M d, Y h:i A');

        $payload = [
            'title' => 'Payment Receipt',
            'message' => 'Thank you for your payment.',
            'status' => $bill->status,
            'reference_no' => $bill->reference_no,
            'amount' => number_format($bill->amount, 2),
            'date_paid' => $paidAtValue,
            'expires_at' => $bill->due_date,
            'payment_id' => $bill->hitpay_reference,
            'bill_month' => $bill->bill_month,
            'name' => $normalizeText($bill->payor ?? $payloadData['name'] ?? ''),
            'email' => $normalizeText($bill->email ?? $payloadData['email'] ?? ''),
            'description' => $description,
            'purpose' => $purpose,
            'payment_method' => $paymentMethod,
        ];

        // Kaelco: breakdown for receipt (Amount + Disconnection Fee + Surcharge = Total)
        if ($bill instanceof KaelcoBill) {
            $amountDue = (float) $bill->amount;
            $disconnectionFee = (float) ($bill->disconnection_fee ?? 0);
            $surcharge = (float) ($bill->surcharge ?? 0);
            $totalAmount = $amountDue + $disconnectionFee + $surcharge;
            $payload['show_breakdown'] = true;
            $payload['amount_due'] = number_format($amountDue, 2);
            $payload['disconnection_fee'] = number_format($disconnectionFee, 2);
            $payload['surcharge'] = number_format($surcharge, 2);
            $payload['amount'] = number_format($totalAmount, 2);
        }

        $pdf = Pdf::loadView('payment.pdf.payment_receipt', compact('payload'))
                ->setPaper('a4','portrait');

        return $pdf->download("payment_receipt_{$bill->hitpay_reference}.pdf");
    }


    /**
     * Extract multiple bill months
     */
    private function extractBillMonthsFromPayments($payments)
    {
        $output = [];

        foreach ($payments as $p) {
            if (!isset($p['period'])) continue;

            $periodRaw = $p['period'];

            // Clean newlines + extra spaces
            $period = trim(preg_replace('/\s+/', ' ', $periodRaw));

            /**
             * Match formats like:
             * "Nov-2025 (202511)"
             */
            if (preg_match('/([A-Za-z]{3}-\d{4})\s*\(\d{6}\)/', $period, $match)) {
                // $match[1] captures "Nov-2025"
                $output[] = $match[1];
            }
        }

        return $output;
    }



    /**
     * Detect which utility a reference number belongs to
     */
    private function detectUtility($reference)
    {
        $ref = strtoupper($reference);

        if (str_contains($ref, 'KAELCO') || str_starts_with($ref, 'KA')) {
            return 'kaelco';
        }

        if (str_contains($ref, 'PELCO')) {
            return 'pelco';
        }

        if (str_contains($ref, 'SRWD') || str_contains($ref, 'STARITA')) {
            return 'srwd';
        }

        if (str_contains($ref, 'MRWD') || str_contains($ref, 'MORONG')) {
            return 'morong';
        }

        return 'unknown';
    }


    private function callKaelcoWebhook($bill)
    {

        // dd($bill);
        try {
            $url = "https://www.kaelco.org/Upgrade/dist/API_TESTING/novupay_webhook.php";

            $payload = [
                "transaction_id"   => $bill->hitpay_reference,
                "reference_number" => $bill->reference_no,
                "status"           => 'completed',
                "payment_method"   => $bill->payment_method ?? 'Online',
            ];

            $response = Http::asForm()->post($url, $payload);
            // dd($response->body());
            Log::info("KAELCO Webhook called", [
                "payload" => $payload,
                "response" => $response->body()
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("KAELCO Webhook Error: " . $e->getMessage());
            return false;
        }
    }
}
