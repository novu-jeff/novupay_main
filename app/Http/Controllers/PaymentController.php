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
                
        $insert = Transactions::create([
            'reference_no' => $payload['reference_no'],
            'amount' => $payload['amount'],
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

        $record = Transactions::where('reference_no', $operation_id)
            ->first();

        if(!$record) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'reference_no ' . $operation_id . ' does not exists'
                ]);
        }

        if($record->date_paid) {
            return response()
                ->json([
                    'status' => 'error',
                    'message' => 'reference_no ' . $operation_id . ' is already paid'
                ]);
        }

        $now = Carbon::now()->format('Y-m-d H:i:s');

        Transactions::where('reference_no', $operation_id)
            ->update([
                'date_paid' => $now,
            ]);

        return response()->json([
            'status' => 'success',
            'message' => 'updated payment info'
        ]);
    }


}
