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


}