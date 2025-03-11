<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{

    public $PaymentService;
    public $username;
    public $passwork;
    public $secretKey;
    public $baseUrl;
    public $serviceID;


    public function __construct(PaymentService $PaymentService) {
        $this->PaymentService = $PaymentService;
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->baseUrl = env('ICOREPAY_BASE_URL');
        $this->serviceID = env('ICOREPAY_SERVICE_ID');
    }

    public function pay() {

        $payload = [
            'service_id' => 'novopay',
            'passwork' => $this->passwork,
            'amount' => 1000,
            'currency' => 'PHP',
            'operation_id' => 'J12',
            'payment_id' => 'J12',
            'by_method' => 'qrph',
            'callback_url' => 'http://your.site/callback_url',
            'return_url' => 'http://your.site/return_url',
            'customer' => [
                'account_number' => '1234567890',
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.dela_cruz@gmail.com',
                'phone_number' => '09167608199',
                'address' => 'Manila, PH',
            ],
        ];
        
        $this->PaymentService->createPayment($payload);

    }

    public function status() {

        $payload = [
            'service_id' => 'novopay',
            'passwork' => $this->passwork,
            'operation_id' => 'J12',         
        ];

        $this->PaymentService->getStatus($payload);

    }

    public function callback() {

        $payload = [
            'service_id' => 'novopay',
            'passwork' => $this->passwork,
            'operation_id' => '04',         
        ];

        $this->PaymentService->getStatus($payload);

    }

}
