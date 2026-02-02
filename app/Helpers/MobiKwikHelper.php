<?php

namespace App\Helpers;

use App\Models\MobikwikToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobiKwikHelper
{
    protected string $baseUrl;
    protected string $publicKey;
    protected string $keyVersion;
    private  $clientId;
    private  $clientSecret;

    public function __construct()
    {
        $this->baseUrl    = config('mobikwik.base_url');
        $this->publicKey  = file_get_contents(config('mobikwik.public_key'));
        $this->keyVersion = config('mobikwik.key_version');
        $this->clientSecret = config('mobikwik.client_secret');
        $this->clientId     = config('mobikwik.client_id');
    }

    function generateMobikwikToken()
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post(
                    config('mobikwik.base_url') . '/recharge/v1/verify/retailer',
                    [
                        'clientId'     => config('mobikwik.client_id'),
                        'clientSecret' => config('mobikwik.client_secret'),
                    ]
                );

            if (!$response->successful()) {
                Log::error('Mobikwik Token API HTTP Error', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to generate token',
                ], $response->status());
            }

            $data = $response->json();

            MobikwikToken::create([
                'token' => $data['data']['token'],
                'creation_time' => now()->toDateTimeString(),
                'response' => json_encode($data),
            ]);

            return $data['data']['token'] ?? null;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            Log::error('Mobikwik Token API Timeout', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection timeout, please try again later',
            ], 504);
        } catch (\Exception $e) {

            Log::error('Mobikwik Token API Exception', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }


    public function sendRequest(string $endpoint, array $payload, string $bearerToken)
    {
        $aesKey = random_bytes(32);
        $iv     = random_bytes(16);

        $encryptedPayload    = $this->aesEncrypt($payload, $aesKey, $iv);
        $encryptedSessionKey = $this->rsaEncrypt($aesKey, $this->publicKey);

        $requestData = [
            'encryptedSessionKey' => base64_encode($encryptedSessionKey),
            'encryptedPayload'    => base64_encode($encryptedPayload),
            'keyVersion'          => $this->keyVersion,
            'iv'                  => base64_encode($iv),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $bearerToken,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . $endpoint, $requestData);

        return $response->json();
    }

    protected function aesEncrypt(array $payload, string $key, string $iv): string
    {
        $plaintext = json_encode($payload);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new \Exception('AES encryption failed');
        }

        return $ciphertext . $tag;
    }

    protected function rsaEncrypt(string $aesKey, string $publicKeyPem): string
    {
        $publicKey = openssl_pkey_get_public($publicKeyPem);

        if (!$publicKey) {
            throw new \Exception('Invalid Mobikwik public key');
        }

        $encrypted = '';
        $ok = openssl_public_encrypt(
            $aesKey,
            $encrypted,
            $publicKey,
            OPENSSL_PKCS1_PADDING
        );

        if (!$ok) {
            throw new \Exception('RSA encryption failed');
        }

        return $encrypted;
    }
}
