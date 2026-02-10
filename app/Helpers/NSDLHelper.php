<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NSDLHelper
{
    private static $username = 'PAYS_955f0c099de0b0b88660212354180494';
    private static $password = 'ca5910b1c0b63c8512e5ebe24920ccaa8660212354192044';
    private static string $base_url = 'https://rafifintech.in/payins';
    public static function processOrderCreation(array $orderData): array
    {
        $orderPayload = [
            'name' => $orderData['name'],          //  name correct
            'mobile_number' => $orderData['mobile'],
            'amount' => $orderData['amount'],
            'transaction_id' => $orderData['transaction_id'],
        ];

        $url = self::$base_url.'/orders';
        $headers = self::getAuthHeaders();

        Log::info('NSDL request', $orderPayload);

        $response = Http::withHeaders($headers)->post($url, $orderPayload);

        Log::info('NSDL response', $response->json() ?? []);

        return $response->json() ?? [
            'status' => false,
            'message' => 'Empty response from NSDL',
        ];
    }

    private static function getAuthHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic '.base64_encode(self::$username.':'.self::$password),
        ];
    }
}
