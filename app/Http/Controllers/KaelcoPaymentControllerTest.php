<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\KaelcoBill;
use App\Models\PaymentItem;
use Carbon\Carbon;

class KaelcoPaymentControllerTest extends Controller
{
    public function receiveEncrypted(Request $request)
    {
        $payload = $request->input('data');
        if (!is_array($payload)) {
            $payload = $request->all();
        }

        Log::info("Incoming KAELCO Test Payload", $payload);

        if (!$payload || !is_array($payload)) {
            return response()->json(['error' => 'Missing payload'], 400);
        }

        $missing = [];
        $invalid = [];
        $referenceNoRaw = $payload['reference_number'] ?? null;
        $amountRaw = $payload['amount'] ?? null;

        if (empty($referenceNoRaw)) {
            $missing[] = 'reference_number';
        }
        if ($amountRaw === null || $amountRaw === '') {
            $missing[] = 'amount';
        } elseif (!is_numeric($amountRaw)) {
            $invalid[] = 'amount';
        }

        if (!empty($missing) || !empty($invalid)) {
            Log::warning('KAELCO Test Missing or invalid required fields', [
                'missing' => $missing,
                'invalid' => $invalid,
                'payload_keys' => array_keys($payload),
            ]);

            return response()->json([
                'error' => 'Missing or invalid required fields',
                'missing' => $missing,
                'invalid' => $invalid,
            ], 400);
        }

        $normalizeText = static function ($value) {
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            if ($value === null) {
                return null;
            }
            if (!is_string($value)) {
                $value = (string) $value;
            }
            return $value === false ? '' : $value;
        };

        $referenceNo = $normalizeText($referenceNoRaw);
        $amountValue = (float) $amountRaw;

        // Use real amount for bill, but test with final HitPay amount = 1 PHP
        $amount = (float) $payload['amount'];

        // ----------------------------------------------------------
        // KAELCO-specific fee computation (test)
        // ----------------------------------------------------------
        $qrph_fee  = $amount <= 2000 ? 20 : ($amount * 0.01);
        $gcash_fee = $amount * 0.023;

        $novupay_fee = 25;
        $c_type = $payload['c_type'] ?? null;
        // Accept any "R" variant (r, R, with spaces, etc.)
        if ($c_type !== null && strtoupper(trim((string) $c_type)) === 'R') {
            $novupay_fee = 10;
        }

        if ($amount < 800) {
            $selected_fee   = $gcash_fee;
            $paymentMethods = ['gcash'];
        } else {
            $selected_fee   = $qrph_fee;
            $paymentMethods = ['qrph_netbank', 'upay_online'];
        }

        // Allow override from payload in tests if needed
        if (!empty($payload['payment_methods']) && is_array($payload['payment_methods'])) {
            $paymentMethods = $payload['payment_methods'];
        }

        $hitpay_fee = round($selected_fee, 1);
        $additional_service_fee = $hitpay_fee + $novupay_fee;

        // For test controller, always send 1 PHP to HitPay
        $final_amount = 1;

        // ----------------------------------------------------------
        // HitPay payload (standalone, no PaymentRequestController)
        // ----------------------------------------------------------
        $account_no = $payload['account_number'] ?? '000000';
        $bill_month = $payload['bill_month'] ?? null;

        try {
            $due_date = !empty($payload['due_date'])
                ? Carbon::parse($payload['due_date'])
                : Carbon::now()->addDays(15);
        } catch (\Exception $e) {
            $due_date = Carbon::now()->addDays(15);
        }
        $due_date_string = $due_date->format('Y-m-d H:i:s');

        $purpose = "Amount Due: ₱" . number_format($amount, 2) . "\n" .
                   "Convenience Fee: ₱" . number_format($additional_service_fee, 2) . "\n" .
                   "KAELCO Test Bill Payment for {$account_no}";

        // HitPay description format (test):
        // Amount Due
        // Convenience Fee
        // Account #
        $descriptionText = "Amount Due: ₱" . number_format($amount, 2) . "\n" .
                           "Convenience Fee: ₱" . number_format($additional_service_fee, 2) . "\n" .
                           "Account #: {$account_no}";

        $email = $payload['email'] ?? 'support@novupay.ph';
        $payor = $payload['name'] ?? 'Kaelco Customer';

        $hitpayPayload = [
            'amount'           => $final_amount,
            'currency'         => 'PHP',
            'email'            => $email,
            'purpose'          => $purpose,
            'reference_number' => $referenceNo,
            'redirect_url'     => env('HITPAY_REDIRECT_URL'),
            'webhook'          => env('HITPAY_WEBHOOK_URL'),
            'name'             => $payor,
            'send_email'       => true,
            'send_sms'         => true,
            'add_admin_fee'    => true,
            'admin_fee'        => $additional_service_fee,
            'expiry_date'      => $due_date_string,
            'description'      => $descriptionText,
            'payment_methods'  => $paymentMethods,
        ];

        Log::info('Sending KAELCO TEST HitPay Request', $hitpayPayload);

        $hitpayResponse = Http::withHeaders([
            'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
        ])->post(env('HITPAY_API_URL') . '/payment-requests', $hitpayPayload);

        if ($hitpayResponse->failed()) {
            Log::error('KAELCO TEST HitPay API failed', ['response' => $hitpayResponse->body()]);
            return response()->json(['error' => 'Failed to contact HitPay'], 500);
        }

        $hitpayData = $hitpayResponse->json();
        Log::info('KAELCO TEST HitPay Data', $hitpayData);

        $accountNoRaw = $payload['account_number'] ?? $referenceNo ?? '000000';
        $account_no = trim($normalizeText($accountNoRaw) ?? '');
        if ($account_no === '') {
            $account_no = '000000';
        }

        // ---------------------------------------
        // Extract Disconnection Fee
        // ---------------------------------------
        $descriptionRaw = $payload['description'] ?? '';
        $disconnectionFee = 0;
        $extractDisconnectionFee = static function ($payments) {
            if (!is_array($payments)) {
                return 0;
            }
            foreach ($payments as $payment) {
                if (!is_array($payment)) {
                    continue;
                }
                $period = $payment['period'] ?? '';
                $amount = $payment['amount'] ?? null;
                if (!is_string($period)) {
                    $period = (string) $period;
                }
                if (stripos($period, 'Disconnection Fee') !== false && is_numeric($amount)) {
                    return (float) $amount;
                }
            }
            return 0;
        };

        if (is_array($descriptionRaw) || is_object($descriptionRaw)) {
            $descriptionArray = is_array($descriptionRaw)
                ? $descriptionRaw
                : (array) $descriptionRaw;
            $disconnectionFee = $extractDisconnectionFee($descriptionArray['payments'] ?? null);
        } else {
            $decodedDescription = json_decode((string) $descriptionRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDescription)) {
                $disconnectionFee = $extractDisconnectionFee($decodedDescription['payments'] ?? null);
            }
        }

        $description = $normalizeText($descriptionRaw);
        if ($disconnectionFee === 0 && $description !== '') {
            if (preg_match(
                    '/Disconnection Fee\s*\(Service Charge\):\s*PHP\s*([0-9]+(?:\.[0-9]{1,2})?)/i',
                    $description,
                    $match
            )) {
                $disconnectionFee = (float) $match[1];
            }
        }

        // ----------------------------------------------------------
        // Save KAELCO bill
        // ----------------------------------------------------------
        $payor = $normalizeText($payload['name'] ?? null);
        $email = $normalizeText($payload['email'] ?? null);
        $contactNo = $normalizeText($payload['phone_number'] ?? null);
        $address = $normalizeText($payload['address'] ?? null);
        $surchargeRaw = $payload['surcharge'] ?? 0;
        $surcharge = is_numeric($surchargeRaw) ? (float) $surchargeRaw : 0;
        $billMonth = $payload['bill_month'] ?? null;
        if (is_array($billMonth) || is_object($billMonth)) {
            $billMonth = json_encode($billMonth);
        }

        $bill = KaelcoBill::create([
            'reference_no'     => $referenceNo,
            'account_no'       => $account_no,
            'payor'            => $payor,
            'email'            => $email,
            'contact_no'       => $contactNo,
            'address'          => $address,
            'amount'           => $amountValue,
            'surcharge'        => $surcharge,
            'bill_month'       => $billMonth,
            'description'      => $description,
            'status'           => 'initiated',
            'hitpay_reference' => $hitpayData['id'] ?? null,
            'hitpay_url'       => $hitpayData['url'] ?? null,
            'payload'          => json_encode($hitpayData),
            'initiated_at'     => now(),
        ]);

        $payment_id = $bill->id;
        $reference_id = $bill->reference_no;

        // dd($disconnectionFee, $payment_id, $reference_id, $payload);
        Log::info("KAELCO Bill Saved", ['bill_id' => $payment_id]);
        Log::info("Disconnection Fee Extracted", ['fee' => $disconnectionFee]);
        Log::info("Preparing to insert Payment Item", [
            'payment_id' => $payment_id,
            'reference_id' => $reference_id,
            'amount' => $disconnectionFee > 0 ? $disconnectionFee : $amountValue,
        ]);


        // ----------------------------------------------------------
        // Insert payment_items (STRICT-SAFE)
        // ----------------------------------------------------------

        $itemType = $disconnectionFee > 0 ? 'reconnection' : 'bill';
        $itemAmount = $disconnectionFee > 0
            ? $disconnectionFee
            : $amountValue;

        PaymentItem::create([
            'payment_id'  => $payment_id,        // REQUIRED FK
            'item_type'   => $itemType,
            'reference_id'=> $reference_id,
            'amount'      => $itemAmount,
        ]);


        $responsePayload = [
            'success'    => true,
            'hitpay_url' => $hitpayData['url'] ?? null,
            'status'     => 'initiated',
            'data'       => $hitpayData,
        ];

        Log::info("KAELCO Test Response Payload", $responsePayload);

        return response()->json($responsePayload);
    }

}
