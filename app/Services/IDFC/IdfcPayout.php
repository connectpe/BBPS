<?php

namespace App\Services\IDFC;

use Exception;



class IdfcPayout
{
    private $privateKey;
    private $publicKey;
    private $client_id;
    private $kid;

    public function __construct()
    {
       
        $this->privateKey = file_get_contents(storage_path('keys/idfc_private_key.pem'));
        

        
        $this->publicKey = file_get_contents(storage_path('keys/idfc_public_key.pem'));
        
        // dd("Public keys is".$this->privateKey);

        $this->client_id = "a0d22ac8-d889-4bb0-ae19-ebecfc8d0ef1";
        $this->kid = "10f3dd6e-68d3-4d6b-b933-78f5e6be101a";
        
        
            // $privateKeyPath = storage_path('keys/idfc_private_key.pem');
            
            
            // $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
            
            // if (!$privateKey) {
            //     throw new \Exception("Invalid private key");
            // }
            
            
            // $keyDetails = openssl_pkey_get_details($privateKey);
            
            // $publicKey = $keyDetails['key']; 
            
            // dd($publicKey);
        

    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        $padding = strlen($data) % 4;
        if ($padding) $data .= str_repeat('=', 4 - $padding);
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function generateJWT()
    {
    // dd($this->kid);
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600;

    $header = [
        "alg" => "RS256",
        "typ" => "JWT",
        "kid" => $this->kid
    ];

    $payload = [
        "jti" => uniqid(),
        "sub" => $this->client_id,
        "iss" => $this->client_id,
        "aud" => "https://app.my.idfcfirstbank.com/platform/oauth/oauth2/token",
        "iat" => $issuedAt,
        "exp" => $expirationTime
    ];
    
    // dd($payload);

    $headerBase64 = $this->base64UrlEncode(json_encode($header));
    $payloadBase64 = $this->base64UrlEncode(json_encode($payload));
    $data = "$headerBase64.$payloadBase64";

    $privateKey = openssl_pkey_get_private($this->privateKey);
    if (!$privateKey) {
        throw new \Exception("Invalid Private Key - check formatting");
    }

    openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
    $signatureBase64 = $this->base64UrlEncode($signature);

    return "$data.$signatureBase64";
}


    public function verifyJWT($jwtToken)
    {
        try {
            list($headerBase64, $payloadBase64, $signatureBase64) = explode('.', $jwtToken);

            $payload = json_decode($this->base64UrlDecode($payloadBase64), true);
            $signature = $this->base64UrlDecode($signatureBase64);

            if ($payload['exp'] < time()) return "JWT Expired!";

            $data = "$headerBase64.$payloadBase64";
            $verified = openssl_verify($data, $signature, $this->publicKey, OPENSSL_ALGO_SHA256);

            return $verified === 1
                ? "JWT Verified!\n" . json_encode($payload, JSON_PRETTY_PRINT)
                : "JWT Verification Failed!";
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

     public function getBearerToken($jwtToken, $scope="paymenttxn-v1fundTransfer paymentenq-paymentTransactionStatus paymentenq-beneValidation cbs-acctenq-accountBalance cbs-acctenq-accountStatement") {
        try {
            $url = "https://apiext.idfcfirstbank.com/authorization/oauth2/token";

            $data = [
                "grant_type" => "client_credentials",
                "scope" => $scope,
                "client_id" => $this->client_id,
                "client_assertion_type" => "urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
                "client_assertion" => $jwtToken
            ];

            $headers = ["Content-Type: application/x-www-form-urlencoded"];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            // dd($result);

            if (!isset($result['access_token'])) {
                throw new Exception("Failed to obtain access token: " . json_encode($result));
            }

            return $result['access_token'];
        } catch (Exception $e) {
            die("OAuth Token Error: " . $e->getMessage());
        }
    }

    private function generateIV()
    {
        return openssl_random_pseudo_bytes(16);
    }

    public function encrypt($data, $hexKey)
    {
        try {
            $key = hex2bin($hexKey);
            if (!$key) throw new Exception("Invalid key format: Must be hex.");

            $iv = $this->generateIV();
            $ciphertext = openssl_encrypt(json_encode($data), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);

            return base64_encode($iv . $ciphertext);
        } catch (Exception $e) {
            die("Encryption Error: " . $e->getMessage());
        }
    }

    public function decrypt($encrypted, $hexKey)
    {
        try {
            $key = hex2bin($hexKey);
            if (!in_array(strlen($key), [16, 24, 32])) throw new Exception("Invalid Key Length, Must be 16/24/32 bytes");

            $data = base64_decode($encrypted);
            $iv = substr($data, 0, 16);
            $ciphertext = substr($data, 16);

            return openssl_decrypt($ciphertext, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
        } catch (Exception $e) {
            echo $e->getMessage();
            return "";
        }
    }

    public function apiRequest($payload, $bearerToken,$Requrl)
    {
        try {
            \Log::info('IDFC Api Call');
            if (!$bearerToken) throw new Exception("Bearer token is missing.");

            $corr_id = "GROSC" . rand(111111111, 999999999);
            $url = $Requrl;
            $headers = [
                "Source: EPO",
                "correlationId: $corr_id",
                "Content-Type: application/octet-stream",
                "Authorization: Bearer $bearerToken"
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            

            if ($http_code !== 200) throw new Exception("API Request Failed with HTTP Code: $http_code | Response: $response");

            // return $response;
            
            return $this->decrypt($response,"9da11d706c65496467234149536b6591a3616d706c65496468634139536b5627");
            
            
        } catch (Exception $e) {
            die("API Request Error: " . $e->getMessage());
        }
    }
}
