<?php

namespace App\Services;

use App\Models\Transactions;
use Carbon\Carbon;
use Exception;

class PaymentService {
    
    public $username;
    public $passwork;
    public $secretKey;
    public $baseUrl;

    public function __construct() {
        $this->username = env('ICOREPAY_USERNAME');
        $this->passwork = env('ICOREPAY_PASSWORK');
        $this->secretKey = env('ICOREPAY_SECRET');
        $this->baseUrl = env('ICOREPAY_BASE_URL');
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

            $ch = curl_init($this->baseUrl . '/pay');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            $decodedResponse = json_decode($response, true);
    
            if ($httpCode === 200) {
                return $decodedResponse;
            }
    
            return [
                'status' => 'fail',
                'error' => $decodedResponse ?? $response 
            ];

        } catch (\Exception $e) {
            return ['status' => 'fail', 'error' => ['message' => $e->getMessage()]];
        }
    }
    
    public function getStatus(array $payload, bool $isApi = false)
    {
        try {
            $payload['signature'] = $this->generateSignature($payload);
            $jsonData = json_encode($payload);
    
            $ch = curl_init($this->baseUrl . '/status');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            $decodedResponse = json_decode($response, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }
    
            $result = ($httpCode === 200) 
                ? $decodedResponse 
                : ['status' => 'fail', 'error' => $decodedResponse ?? $response];
    
            $record = Transactions::select('date_paid', 'payment_id', 'reference_no', 'callback')->where('payment_id', $payload['operation_id'])
                ->first();

            $status = $result['operation']['status'];
            $external_id = $result['external_id'];
            $reference_no = $record['reference_no'];
            $payment_id = $record['payment_id'];
            $date_paid = $record['date_paid'] ? Carbon::parse($record['date_paid'])->format('M d, Y h:i A') : '';

            $response = [
                'status' => $status,
                'external_id' => $external_id,
                'reference_no' => $reference_no,
                'payment_id' => $payment_id,
                'amount' => $result['amount'],
                'date_paid' => $date_paid,
                'callback' => [
                    'external' => $record['callback'],
                    'internal' => route('payment.callback')
                ] ?? []
            ];

            if ($isApi) {
                echo json_encode($response);
                return;
            }
    
            return $response;
    
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