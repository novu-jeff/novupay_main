<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentNavigationController extends Controller
{

    public $PaymentService;
    public $merchant;
    public $username;
    public $passwork;
    public $secretKey;
    public $payInUrl;
    public $payOutUrl;
    public $statusUrl;

    public function __construct(PaymentService $PaymentService) {
        $this->PaymentService = $PaymentService;
        $this->merchant = env('ICOREPAY_MERCHANT');
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->payInUrl = env('ICOREPAY_PAYIN');
        $this->payOutUrl = env('ICOREPAY_PAYOUT');
        $this->statusUrl = env('ICOREPAY_STATUS');
    }

    public function show(string $transaction_id) {
    
        $data = Transactions::where('reference_no', $transaction_id)->first();

        if (!$data) {
            return abort(404, 'Transaction not found.');
        }
        
        $response = $this->getStatus($data->payment_id ?? '');
        if(!isset($response['external_id'])) {
            return view('payment.index', ['transaction_id' => $transaction_id]);
        } else {
            return redirect()->route('payment.merchants.pay', ['transaction_id' => $transaction_id, 'operation_id' => $data->payment_id]);
        }
    }

    public function pay(string $transaction_id, string $operation_id) {

        $response = $this->getStatus($operation_id);

        if(empty($response)) {
            return redirect()->route('payment.merchants.show', ['transaction_id' => $transaction_id, 'operation_id' => $operation_id]);
        }

        $payload = $response;

        $payload['reference_no'] = $transaction_id;

        return view('payment.merchant', compact('payload'));
    }
    
    public function store(Request $request, string $transaction_id) {
        
        $payload = $request->all();

        $validator = Validator::make([
            'transaction_id' => $transaction_id,
            'by_method' => $payload['by_method']
        ], [
            'transaction_id' => 'required|exists:transactions,reference_no',
            'by_method' => 'required|in:qrph,gcash-app,gcash,maya,grabpay,pm-,pm-dob-mbnk,pm-dob-bdo,pm-dob-bpi,pm-dob-lbnk,pm-dob-ubp',
        ]);

        if($validator->fails()) {
            return [
                'status' => 'error',
                'errors' => $validator->errors() 
            ];
        }

        $model = Transactions::where('reference_no', $transaction_id)->first();

        if (!$model) {
            return [
                'status' => 'error',
                'message' => 'Transaction not found'
            ];
        }
        
        $unique = $this->generatePaymentID();

        $amount = $model->amount;
        
        $data['operation_id'] = $unique;
        $data['payment_id'] = $unique;
        $data['service_id'] = $this->username;
        $data['passwork'] = $this->passwork;
        $data['callback_url'] = env('CALLBACK_URL');
        $data['return_url'] = env('CALLBACK_URL');
        $data['amount'] = $amount;
        $data['currency'] = 'PHP';
        $data['by_method'] = $payload['by_method'];
        $data['merchant'] = [
            'name' => $this->merchant,
        ];

        $response = $this->PaymentService->createPayment($data);

        if(!isset($response['request'])) {
            if($response['status'] == 'error') {
                return response()->json($response);
            }
        }

        $model->by_method = $data['by_method'];
        $model->payment_id = $data['payment_id'];
        $model->external_id = $response['external_id'];
        $model->operation_id = $response['operation_id'];
        $model->save();

        return redirect()->route('payment.merchants.pay', ['transaction_id' => $transaction_id, 'operation_id' => $unique]);

    }

    public function choose_other(string $payment_id, string $reference_no) {
        $record = Transactions::where('reference_no', $reference_no)
            ->where('payment_id', $payment_id)
            ->first();

        if($record) {

            $record->payment_id = null;
            $record->by_method = null;
            $record->external_id = null;
            $record->operation_id = null;
            $record->save();

            return redirect()->route('payment.merchants.show', ['transaction_id' => $reference_no]);
        }

        return 'error';
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
    
    private function generatePaymentID() {
        return now()->format('YmdHis') . '-' . Str::uuid()->toString(8);
    }

    private function selectMerchant(string $merchant) {

        switch($merchant) {
            
            case 'gcash-app': 
                return 'Gcash Payment';
            case 'gcash':
                return 'Gcash Payment';
            case 'maya':
                return 'Maya Payment';
            case 'grabpay':
                return 'Grabpay Payment';
            case 'qrph':
                return 'QRPH Payment';
            
        }

    }

    private function getPaymentLogo(string $payment_type) {
        $list = [
            'gcash-app' => 'other-banks/gcash.png',
            'gcash' => 'other-banks/gcash.png',
            'maya' => 'banks/mayabank.png',
            'grabpay' => 'other-banks/grab pay.png',
            'qrph' => 'other-banks/qrph.png'
        ];

        return $list[$payment_type];
    }

}
