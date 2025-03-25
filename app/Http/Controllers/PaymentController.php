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

    public function __construct(PaymentService $PaymentService) {
        $this->PaymentService = $PaymentService;
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->baseUrl = env('ICOREPAY_BASE_URL');
    }

    public function saveTransaction(Request $request) {

        $payload =  $request->all();
                
        $callback = $payload['callback'];

        unset($payload['callback']);

        $insert = Transactions::create([
            'reference_no' => $payload['reference_no'],
            'content' => json_encode($payload),
            'callback' => $callback
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

    public function pay(Request $request) {

        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'operation_id' => 'required',
            'payment_id' => 'required',
            'by_method' => 'required|in:gcash-app,qrph',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $payload = [
            'service_id' => $this->username,
            'passwork' => $this->passwork,
            'amount' => $request->amount,
            'currency' => 'PHP',
            'operation_id' => $request->operation_id,
            'payment_id' => $request->payment_id,
            'by_method' => $request->by_method,
            'callback_url' => env('CALLBACK_URL'),
            'return_url' => env('CALLBACK_URL'),
            'customer' => [
                'account_number' => $request->customer['account_number'] ?? '',
                'name' => $request->customer['name'] ?? '',
                'email' => $request->customer['email'] ?? '',
                'phone_number' => $request->customer['phone_number'] ?? '',
                'address' => $request->customer['address'] ?? '',
            ],
        ];

        $this->PaymentService->createPayment($payload);
    }

    public function status(Request $request, string $external_id) {

        $isApi = $request->isApi ?? false;

        $payload = [
            'service_id' => $this->username,
            'passwork' => $this->passwork,
            'operation_id' => $external_id,         
        ];

        $this->PaymentService->getStatus($payload, $isApi);

    }

    public function callback(Request $request) {

        $payload = $request->all();

        $record = Transactions::where('reference_no', $payload['reference_no'])
            ->first();

        if(!$record) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'reference_no ' . $payload['reference_no'] . ' does not exists'
                ]);
        }

        if($record->date_paid) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'reference_no ' . $payload['reference_no'] . ' is already paid'
                ]);
        }

        $now = Carbon::now()->format('Y-m-d H:i:s');

        Transactions::where('reference_no', $payload['reference_no'])
            ->update([
                'date_paid' => $now,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'updated payment info'
        ]);
    }


}
