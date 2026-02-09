<?php

namespace App\Helpers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class NSDLHelper
{
    private $username = "M2M_4e36cfdba50769e047354341880921692";
    private $password = "447a00a55be084af370a6bc2953e951d47354341880940651";
    private $base_url = "https://rafifintech.in/payins";
    public $referenceId;

    public static function processOrderCreation( $orderData): array
    {
    

        $orderPayload = [
            "name"       => $orderData['amount'],
            "mobile_number"      => $orderData['mobile'],
            "amount"         => $orderData['amount'],
            "transaction_id"    => $orderData['transaction_id'],
            
        ];

        $url = $base_url . "/orders";
        $headers = getAuthHeaders();
        
        
        \Log::info('request is',[json_encode($orderPayload)]);

        

        $result = Http::withHeaders($headers)
                ->post($url, $orderPayload);
      
        return ['data' => json_decode($result['response'])];
    }
   
    private function createErrorResponse(string $status, string $message): array
    {
        return [
            'data' => (object)[
                'statuscode' => self::STATUS_ERROR,
                'status' => $status,
                'message' => $message,
                'trans_status' => 'failed',
                'refno' => null,
                'rrn' => null
            ]
        ];
    }
    
    /**
     * Get authentication headers for API requests
     *
     * @return array Headers array
     */
    private function getAuthHeaders(): array
    {
        return [
            "Content-Type:application/json",
            "Authorization: Basic " .base64_encode("$this->username:$this->password"),
        ];
    }
    
   
}