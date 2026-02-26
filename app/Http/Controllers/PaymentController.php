<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Models\Bill;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{

    public $PaymentService;
    public $merchant;
    public $username;
    public $passwork;
    public $secretKey;
    public $baseUrl;
    public $appCallback;

    public function __construct(PaymentService $PaymentService) {
        $this->PaymentService = $PaymentService;
        $this->merchant = env('ICOREPAY_MERCHANT');
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->baseUrl = env('ICOREPAY_BASE_URL');
        $this->appCallback = env('ICOREPAY_CALLBACK');
    }

    public function saveTransaction(Request $request) {

        $payload =  $request->all();
                
        $insert = Transactions::create([
            'reference_no' => $payload['reference_no'],
            'amount' => $payload['amount'],
            'payment_id' => $payload['payment_id'] ?? null,
            'by_method' => $payload['by_method'] ?? null,
            'external_id' => $payload['external_id'] ?? null,
            'callback_url' => $payload['callback_url'] ?? null
        ]);

        if (!$insert) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'Error occurred: unable to insert transaction',
                ]);
        }

        if(isset($payload['isExternal']) && $payload['isExternal']) {

            $unique = $this->generatePaymentID();

            $callback = $this->appCallback;
            
            $amount = $payload['amount'];
            
            $data['operation_id'] = $unique;
            $data['payment_id'] = $unique;
            $data['service_id'] = $this->username;
            $data['passwork'] = $this->passwork;
            $data['callback_url'] = $callback;
            $data['return_url'] = $callback;
            $data['amount'] = $amount;
            $data['currency'] = 'PHP';
            $data['by_method'] = $payload['by_method'];
            $data['merchant'] = [
                'name' => $this->merchant,
            ];

            $response = $this->PaymentService->createPayment($data);

            return response()
                ->json($response);

        }

        return response()
            ->json([
                'status' => 'success',
                'reference_no' => $payload['reference_no'],
                'message' => 'inserted',
            ]);


    }

    private function generatePaymentID() {
        // Use full UUID; no length argument is supported on toString()
        return now()->format('YmdHis') . '-' . Str::uuid()->toString();
    }

    public function callback($operation_id) {

        $response = $this->getStatus($operation_id);

        if($response && isset($response['operation']) && $response['operation']['status'] == 'paid') {
            $this->initPaid($response, $operation_id);
            $transaction = Transactions::where('operation_id', $operation_id)->first();
            return redirect()->route('payment.merchants.pay', ['transaction_id' => $transaction->reference_no, 'operation_id' => $operation_id]);
        } 
    }

    private function getStatus(string $operation_id) {

        $payload = [
            'service_id' => $this->username,
            'passwork' => $this->passwork,
            'operation_id' => $operation_id,         
        ];
    
        $response = $this->PaymentService->getStatus($payload);
    
        return $response ?? []; 
    }

    private function initPaid(array $payload, string $operation_id) {

        $novupay_transaction = Transactions::where('operation_id', $operation_id)->first();
        
        if($novupay_transaction) {
            
            $reference_no = $novupay_transaction->reference_no;

            $date_paid = $novupay_transaction->date_paid;

            if(is_null($date_paid)) {

                $novupay_transaction->date_paid = Carbon::now();
                $novupay_transaction->save();
                
                $app_api = $this->appCallback . '/' . $reference_no;

                $ch = curl_init($app_api);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        
                $response = curl_exec($ch);
                curl_close($ch);
        
                $decodedResponse = json_decode($response, true);

            }
        }        
    }

    public function handleQrPayment(Request $request)
    {
        $reference = $request->query('ref');
        $amount = $request->query('amount');
        $account = $request->query('account_no');

        if (!$reference || !$amount) {
            abort(400, 'Missing required parameters.');
        }

        $bill = Bill::firstOrCreate(
            ['reference_no' => $reference],
            [
                'account_no' => $account,
                'amount' => $amount,
                'status' => 'pending',
            ]
        );

        // ✅ Call HitPay
        $hitpayData = $this->createHitpayPaymentRequest([
            'amount' => $bill->amount,
            'reference_no' => $bill->reference_no,
            'name' => 'Bill #' . $bill->reference_no,
        ]);

        if ($hitpayData && !empty($hitpayData['url'])) {
            $bill->update([
                'initiated_at' => now(),
                'hitpay_reference' => $hitpayData['id'] ?? null,
                'hitpay_url' => $hitpayData['url'],
                'payload' => $hitpayData,
            ]);

            // Redirect user directly to HitPay checkout
            return redirect()->away($hitpayData['url']);
        }

        return response()->json(['error' => 'Failed to create HitPay request'], 500);
    }


    protected function createHitpayPaymentRequest(array $data)
    {
        $apiKey = config('services.hitpay.api_key') ?? env('HITPAY_API_KEY');
        $merchantId = config('services.hitpay.merchant_id') ?? env('HITPAY_MERCHANT_ID');
        $url = env('HITPAY_URL', 'https://api.hit-pay.com/v1/payment-requests');

        $payload = [
            'amount' => $data['amount'],
            'currency' => 'PHP',
            'reference_number' => $data['reference_no'],
            'redirect_url' => env('HITPAY_REDIRECT_URL'),
            'webhook' => env('HITPAY_WEBHOOK_URL'),
            'name' => $data['name'] ?? 'Novupay Bill Payment',
            'purpose' => $data['purpose'] ?? 'Bill payment',
        ];

        try {
            $response = Http::withHeaders([
                'X-BUSINESS-API-KEY' => $apiKey,
                'X-Requested-With' => 'XMLHttpRequest',
            ])->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                \Log::error('HitPay API Error', ['body' => $response->body()]);
                return null;
            }
        } catch (\Throwable $th) {
            \Log::error('HitPay Exception', ['message' => $th->getMessage()]);
            return null;
        }
    }


}
