<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    public $PaymentService;
    public $username;
    public $passwork;
    public $secretKey;
    public $baseUrl;
    public $appCallback;

    public function __construct(PaymentService $PaymentService) {
        $this->PaymentService = $PaymentService;
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->baseUrl = env('ICOREPAY_BASE_URL');
        $this->appCallback = env('APP_CALLBACK');
    }

    public function saveTransaction(Request $request) {

        $payload =  $request->all();
                
        $insert = Transactions::create([
            'reference_no' => $payload['reference_no'],
            'amount' => $payload['amount'],
            'payment_id' => $payload['payment_id'] ?? null,
            'by_method' => $payload['by_method'] ?? null,
            'external_id' => $payload['external_id'] ?? null
        ]);

        if (!$insert) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'Error occurred: unable to insert transaction',
                ]);
        }

        return response()
            ->json([
                'status' => 'success',
                'reference_no' => $payload['reference_no'],
                'message' => 'inserted',
            ]);
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


}
