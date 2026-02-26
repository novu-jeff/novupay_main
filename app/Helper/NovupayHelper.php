<?php

use Illuminate\Support\Facades\Config;

if (!function_exists('generateSignedQrUrl')) {
    function generateSignedQrUrl(array $data): string
    {
        $secret = env('APP_SIGNATURE_KEY');
        $query = http_build_query($data);
        $signature = hash_hmac('sha256', $query, $secret);

        return rtrim(env('APP_URL'), '/') . '/api/payment-request?' . $query . '&sig=' . $signature;
    }
}

function encryptPayload(array $payload): string
{
    $key = base64_decode(str_replace('base64:', '', env('PAYLOAD_ENCRYPTION_KEY')));
    $iv  = base64_decode(str_replace('base64:', '', env('PAYLOAD_ENCRYPTION_IV')));

    $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
    $encrypted = openssl_encrypt($json, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $base64 = base64_encode($encrypted);

    // URL-encode for safe transport
    return urlencode($base64);
}


if (!function_exists('decryptPayload')) {
    function decryptPayload(?string $encrypted): ?array
    {
        try {
            if (!$encrypted) {
                \Log::warning('decryptPayload: empty input');
                return null;
            }

            $encrypted = urldecode(trim($encrypted));
            $encrypted = str_replace(' ', '+', $encrypted);

            $key = base64_decode(str_replace('base64:', '', env('PAYLOAD_ENCRYPTION_KEY')));
            $iv  = base64_decode(str_replace('base64:', '', env('PAYLOAD_ENCRYPTION_IV')));

            \Log::info('decryptPayload check', [
                'key_len' => strlen($key),
                'iv_len'  => strlen($iv),
                'sample'  => substr($encrypted, 0, 30)
            ]);

            // Decode Base64
            $decoded = base64_decode($encrypted, true);
            \Log::info("decoded result", ['decoded' => $decoded]);

            if ($decoded === false) {
                \Log::warning('decryptPayload: base64_decode failed');
                return null;
            }

            // Decrypt
            $decrypted = openssl_decrypt($decoded, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            \Log::info("decrypted result", ['decrypted' => $decrypted]);

            if ($decrypted === false) {
                \Log::warning('decryptPayload: openssl_decrypt failed');
                return null;
            }

            \Log::info('decryptPayload raw output', [
                'out' => $decrypted
            ]);

            $json = json_decode($decrypted, true);
            \Log::info('decryptPayload json', ['json' => $json]);

            return $json;
        } catch (\Throwable $e) {
            \Log::error('decryptPayload exception: '.$e->getMessage());
            return null;
        }
    }
}

