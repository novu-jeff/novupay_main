<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

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
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            // Decode response if it's valid JSON
            $decodedResponse = json_decode($response, true);
            
            // Set JSON header
            header('Content-Type: application/json');
    
            if ($httpCode === 200) {
                echo json_encode($decodedResponse);
                exit;
            }
    
            echo json_encode([
                'status' => 'fail',
                'error' => $decodedResponse ?? $response 
            ]);

            exit;

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'fail', 'error' => ['message' => $e->getMessage()]]);
            exit;
        }
    }
    
    public function getStatus(array $payload) {
        try {
            
            $payload['signature'] = $this->generateSignature($payload);
            $jsonData = json_encode($payload);
    
            $ch = curl_init($this->baseUrl . '/status');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            // Decode response if it's valid JSON
            $decodedResponse = json_decode($response, true);
            
            // Set JSON header
            header('Content-Type: application/json');
    
            if ($httpCode === 200) {
                echo json_encode($decodedResponse);
                exit;
            }
    
            echo json_encode([
                'status' => 'fail',
                'error' => $decodedResponse ?? $response 
            ]);

            exit;

        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'fail', 'error' => ['message' => $e->getMessage()]]);
            exit;
        }
    }

}