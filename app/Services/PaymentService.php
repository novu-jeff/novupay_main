<?php

namespace App\Services;

use App\Models\Transactions;
use Carbon\Carbon;
use Exception;

class PaymentService {
    
    public $username;
    public $passwork;
    public $secretKey;
    public $status;
    public $payUrl;

    public function __construct() {
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->status = env('ICOREPAY_STATUS');
        $this->payUrl = env('ICOREPAY_PAY');
    }

    public function generateSignature(array $data) {

        $secretKey = $this->secretKey;

        $signature = "";

        foreach ($data as $key => $value) {
            if ($key === "signature") {
                continue;
            }
    
            if (empty($value)) {
                $value = "-";
            }
    
            if (is_array($value)) {
                foreach ($value as $subValue) {
                    $signature .= empty($subValue) ? "-" : $subValue;
                }
            } else {
                $signature .= $value;
            }
        }
    
        return strtolower(hash_hmac('sha256', $signature, $secretKey));
        
    }

    public function createPayment(array $payload) {

        try {

            $payload['signature'] = $this->generateSignature($payload);
            $jsonData = json_encode($payload);

            $ch = curl_init($this->payUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    
            $response = curl_exec($ch);
            curl_close($ch);
    
            $decodedResponse = json_decode($response, true);

            if(!empty($decodedResponse) && $decodedResponse['request']['status'] == 'error') {
                return [
                    'status' => 'error',
                    'error_code' => $decodedResponse['request']['error_code'] ?? 'Unknown error',
                    'error_message' => $decodedResponse['request']['error_message'] ?? 'Failed to create payment'
                ];
            }

           return $decodedResponse;

        } catch (\Exception $e) {
            return ['status' => 'fail', 'error' => ['message' => $e->getMessage()]];
        }
    }
    
    public function getStatus(array $payload, bool $isApi = false)
    {
        try {
            $payload['signature'] = $this->generateSignature($payload);
            $jsonData = json_encode($payload);
    
            $ch = curl_init($this->status);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            $decodedResponse = json_decode($response, true);
    
            return $decodedResponse;
    
        } catch (\Exception $e) {
            $errorResult = ['status' => 'fail', 'error' => ['message' => $e->getMessage()]];
            
            if ($isApi) {
                echo json_encode($errorResult);
                return;
            }
            
            return $errorResult;
        }
    
    }

    public function createHitpayPaymentRequest(string $reference_no, array $payload): ?array
    {
        try {
            $result = $this->getBill($reference_no, $payload, false);

            if (isset($result['error'])) {
                \Log::error('HitPay error: ' . $result['error']);
                return null;
            }

            $billData = $result['data']['current_bill'] ?? null;
            if (!$billData) {
                \Log::error('Missing bill data for HitPay', ['reference_no' => $reference_no]);
                return null;
            }

            $amount = number_format((float)$billData['amount'], 2, '.', '');
            if($amount <= 2000) {
                $hitpay_fee = 20;
            }else {
                $hitpay_fee = ($amount * 0.01);
            }
            $novupay_fee = 10;
            $additional_service_fee = $hitpay_fee + $novupay_fee;

            $final_amount = (float)$amount + $additional_service_fee;
            
            // Apply testing mode if enabled
            if (env('NOVUPAY_FINAL_AMOUNT') === 'Testing') {
                $final_amount = 1;
            }

            $payor = $result['data']['client']['name'] ?? ($payload['payor'] ?? 'Sta. Rita Customer');
            $email = $result['data']['client']['email'] ?? ($payload['email'] ?? 'srwdsystem2023@gmail.com');
            $account_no = $result['data']['client']['account_no'] ?? ($payload['account_no'] ?? '000000');

            // 🧾 Purpose formatting
            $purpose = "Amount Due: PHP {$amount}\nConvenience Fee: PHP {$additional_service_fee}\nAccount #: {$account_no}";

            // ⚙️ Default payment methods (include QRPH if allowed)
            // $paymentMethods = ["gcash","gcash_qr","qrph_netbank","upay_bayd","upay_ecpy","upay_instapay","upay_online","upay_pchc","upay_plwn","xpay_card"];
            $paymentMethods = ['gcash', 'qrph_netbank'];
            // dd($final_amount, $paymentMethods);

            // 🚫 If total amount < 800, remove QRPH from payment options
            if ($final_amount < 800) {
                $paymentMethods = array_filter($paymentMethods, fn($m) => $m !== "qrph_netbank");
                \Log::info('Removed QRPH (amount < 800)', [
                    'reference_no' => $reference_no,
                    'amount' => $final_amount
                ]);
            // removed gcash since it is costing us 2.5% unlike qrph which is only 1% or 20php per transaction
            } else {
                $paymentMethods = array_filter($paymentMethods, fn($m) => $m !== "gcash");
            }

            $hitpayPayload = [
                'amount' => $final_amount,
                'currency' => 'PHP',
                'email' => $email,
                'purpose' => $purpose,
                'reference_number' => $reference_no,
                'redirect_url' => env('HITPAY_REDIRECT_URL'),
                'webhook' => env('HITPAY_WEBHOOK_URL'),
                'send_email' => true,
                'send_sms' => true,
                'name' => $payor,
                'add_admin_fee' => true,
                'admin_fee' => '15.00',
                'payment_methods' => array_values($paymentMethods),
            ];

            // dd($hitpayPayload);

            $response = \Http::withHeaders([
                'X-BUSINESS-API-KEY' => env('HITPAY_API_KEY'),
            ])->post(env('HITPAY_API_URL') . '/payment-requests', $hitpayPayload);

            // dd($response->body());
            if ($response->failed()) {
                \Log::error('HitPay API request failed', ['body' => $response->body()]);
                return null;
            }

            $data = $response->json();
            return [
                'id' => $data['id'] ?? null,
                'url' => $data['url'] ?? null,
            ];
        } catch (\Exception $e) {
            \Log::error('createHitpayPaymentRequest exception: ' . $e->getMessage());
            return null;
        }
    }


}