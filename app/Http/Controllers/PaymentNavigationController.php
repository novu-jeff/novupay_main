<?php

namespace App\Http\Controllers;

use App\Models\Transactions;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class PaymentNavigationController extends Controller
{

    public $PaymentService;
    public $username;
    public $passwork;
    public $secretKey;
    public $baseUrl;

    public function __construct(PaymentService $PaymentService) {
        $this->PaymentService = $PaymentService;
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->baseUrl = env('ICOREPAY_BASE_URL');
    }

    public function show(string $transaction_id) {
        
        $data = Transactions::where('reference_no', $transaction_id)->first();
    
        if (!$data) {
            return abort(404, 'Transaction not found.');
        }
    
        $response = $this->getStatus($data->payment_id ?? $data->reference_no);
    
        if (isset($response) && $response['status'] == 'processing' && $data['request']) {
    
            $content = json_decode($data['request'], true);

            $qrContent = $content['qr_content'] ?? null;
            $operationId = $content['operation_id'] ?? null;

            if ($qrContent && $operationId) {

                $qrCode = QrCode::size(180)->generate($qrContent);
    
                $payload = [
                    'merchant' => $this->selectMerchant($data['by_method']),
                    'qr_code' => $qrCode,
                    'reference_no' => $data->reference_no,
                    'operation_id' => $operationId,
                    'external_id' => $response['external_id']
                ];
    
                return view('payment.merchant', compact('payload'));
            }
        }

        if(isset($response) && $response['status'] != 'processing') {

            $status = $response['status'];

            $payload = [];

            if($status == 'paid') {
                $payload = [
                    'status' => $status,
                    'title' => 'Payment Success!',
                    'message' => 'Your payment has been successfully processed.',
                    'reference_no' => $data->reference_no,
                    'payment_id' => $data->payment_id,
                    'date_paid' => $response['date_paid'],
                    'amount' => 'PHP ' . number_format($response['amount'], 2),
                ];
            } else {
                return view('payment.index', ['transaction_id' => $transaction_id]);
            }

            return view('payment.status', compact('payload'));
        }
    
        return view('payment.index', ['transaction_id' => $transaction_id]);
    }
    
    public function store(Request $request, string $transaction_id) {
        
        $payload = $request->all();

        $validator = Validator::make([
            'transaction_id' => $transaction_id,
            'by_method' => $payload['by_method']
        ], [
            'transaction_id' => 'required|exists:transactions,reference_no',
            'by_method' => 'required|in:qrph,gcash-app'
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

        $data = json_decode($model->content, true) ?? [];

        if (!is_array($data)) {
            throw new Exception("Invalid JSON data structure.");
        }
        
        if (isset($payload['by_method'])) {
            $data['by_method'] = $payload['by_method'];
        }
        
        $unique = $this->generatePaymentID();

        $data['operation_id'] = $unique;
        $data['payment_id'] = $unique;
        $data['service_id'] = $this->username;
        $data['passwork'] = $this->passwork;
        $data['callback_url'] = env('CALLBACK_URL');
        $data['return_url'] = env('CALLBACK_URL');
        $data['currency'] = 'PHP';


        $response = $this->PaymentService->createPayment($data);
        

        $model->by_method = $data['by_method'];
        $model->payment_id = $data['payment_id'];
        $model->request = json_encode($response);
        $model->save();

        return redirect()->route('payment.merchants.show', ['transaction_id' => $transaction_id]);

    }

    public function choose_other(string $payment_id, string $reference_no) {
        $record = Transactions::where('reference_no', $reference_no)
            ->where('payment_id', $payment_id)
            ->first();
        if($record) {
            $record->payment_id = null;
            $record->by_method = null;
            $record->request = null;
            $record->save();

            return redirect()->back();
        }

        return 'error';
    }

    private function getStatus(string $operation_id)
    {
        $payload = [
            'service_id' => $this->username,
            'passwork' => $this->passwork,
            'operation_id' => $operation_id,         
        ];
    
        $response = $this->PaymentService->getStatus($payload);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to decode JSON response: " . json_last_error_msg());
        }
    
        return $response ?? []; 
    }
    
    private function generatePaymentID() {
        return now()->format('YmdHis') . '-' . Str::uuid()->toString(8);
    }

    private function selectMerchant(string $merchant) {

        switch($merchant) {
            
            case 'gcash-app': 
                return 'Gcash Payment';
            case 'qrph':
                return 'QRPH Payment';
            
        }

    }

}
