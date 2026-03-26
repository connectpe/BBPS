<?php

namespace App\Helpers;

use App\Models\Ladger;
use App\Models\MobikwikToken;
use App\Models\Transaction;
use App\Models\UserService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MobiKwikHelper
{
    protected string $baseUrl;
    protected string $publicKey;
    protected string $keyVersion;
    private  $clientId;
    private  $clientSecret;
    private  $paymentAccountInfo;

    public function __construct()
    {
        $this->baseUrl    = config('mobikwik.base_url');
        $this->publicKey  = file_get_contents(config('mobikwik.public_key'));
        $this->keyVersion = config('mobikwik.key_version');
        $this->clientSecret = config('mobikwik.client_secret');
        $this->clientId     = config('mobikwik.client_id');
        $this->paymentAccountInfo = config('mobikwik.payment_account_info');
    }

    public function generateToken()
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/recharge/v1/verify/retailer', [
                    'clientId' => $this->clientId,
                    'clientSecret' => $this->clientSecret,
                ]);

            // dd($this->clientId,$this->clientSecret);

            if (! $response->successful()) {
                Log::error('Mobikwik Token API HTTP Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                throw new \Exception('Token API failed');
            }

            $data = $response->json();
            $token = $data['data']['token'];

            //  OLD TOKEN HANDLE (rotation rule)
            MobikwikToken::where('is_active', true)
                ->update([
                    'is_active' => false,
                    'expire_at' => now()->addMinutes(5), // old token only 5 min
                    'rotated_at' => now(),
                ]);

            // NEW TOKEN SAVE
            MobikwikToken::create([
                'token' => $token,
                'expire_at' => now()->addHours(24),
                'is_active' => true,
                'response' => json_encode($data),
                'creation_time' => now(),
            ]);

            return $token; // ONLY TOKEN RETURN

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Mobikwik Token API Timeout', [
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Connection timeout');
        } catch (\Exception $e) {
            Log::error('Mobikwik Token API Exception', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function isTokenPresent()
    {
        try {
            $tokenData = MobikwikToken::where('is_active', true)
                ->where('expire_at', '>=', now())
                ->latest()
                ->first();
            // dd($tokenData->token);

            // if valid token found
            if ($tokenData) {
                return $tokenData->token;
            }

            //  if token not found → new generate
            return (new MobiKwikHelper)->generateToken();
        } catch (\Exception $e) {
            Log::error('Mobikwik Token Present Exception', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendRequest(string $endpoint, array $payload, string $bearerToken)
    {
        // dd($bearerToken);
        $aesKey = random_bytes(32);
        $iv     = random_bytes(16);

        $encryptedPayload    = $this->aesEncrypt($payload, $aesKey, $iv);
        $encryptedSessionKey = $this->rsaEncrypt($aesKey, $this->publicKey);
        // dd($encryptedPayload); 
        $requestData = [
            'encryptedSessionKey' => base64_encode($encryptedSessionKey),
            'encryptedPayload'    => base64_encode($encryptedPayload),
            'keyVersion'          => $this->keyVersion,
            'iv'                  => base64_encode($iv),
        ];

        // dd($requestData);
        $response = Http::withHeaders([
            'Authorization' => $bearerToken,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl . $endpoint, $requestData);
        // dd($response->json());
        return $response->json();
    }

    public static function aesEncrypt(array $payload, string $key, string $iv): string
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

    public static function rsaEncrypt(string $aesKey, string $publicKeyPem): string
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

    public function Payment(string $endpoint, $mobile, $operatorId, $circleId, $planId, $amount)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            $connectPeId = CommonHelper::generateConnectPeTransactionId();
            $reqId = CommonHelper::generateTransactionId();
            $paymentRefId = CommonHelper::generatePaymentRefId();
            $userService = (int) 1;

            $payload = [
                'cn'                  => $mobile,
                'op'                  => $operatorId,
                'cir'                 => $circleId,
                'amt'                 => $amount,
                'customerMobile'      => $mobile,
                'remitterName'        => $user->name,
                'paymentMode'         => 'Wallet',
                'paymentAccountInfo'  => $this->paymentAccountInfo,
                'reqid'               => $reqId,
                'paymentRefID'        => $paymentRefId,
                'plan_id'             => $planId,
                'userid'              => $user->id,
                'connectpeId'         => $connectPeId,
                'call'                => 'balance_debit',
            ];

            // Step 1: Create Transaction
            Transaction::create([
                'user_id'               => $user->id,
                'operator_id'           => $payload['op'],
                'circle_id'             => $payload['cir'],
                'amount'                => $payload['amt'],
                'transaction_type'      => $payload['paymentMode'],
                'request_id'            => $payload['reqid'],
                'mobile_number'         => $payload['customerMobile'],
                'payment_ref_id'        => $payload['paymentRefID'],
                'payment_account_info'  => $payload['paymentAccountInfo'],
                'recharge_type'         => 'prepaid',
                'connectpe_id'          => $connectPeId,
            ]);

            // Step 2: Debit Wallet via Stored Procedure
            $result = DB::select(
                "CALL debitAmountFromUserWallet(?, ?, ?, ?)",
                [
                    $user->id,
                    $amount,
                    $userService,
                    false
                ]
            );

            if (empty($result) || empty($result[0]->response)) {
                throw new \Exception("Invalid response from wallet debit procedure");
            }

            $response = json_decode($result[0]->response, true);

            if (!$response || !isset($response['opening_balance'], $response['remaining_balance'])) {
                throw new \Exception("Invalid wallet response structure");
            }

            $ledger = Ladger::create([
                'reference_no'      => $paymentRefId,
                'request_id'        => $reqId,
                'connectpe_id'      => $connectPeId,
                'user_id'           => $user->id,
                'txn_amount'        => $amount,
                'txn_date'          => now(),
                'txn_type'          => 'dr',
                'service_id'        => $userService,
                'opening_balance'   => $response['opening_balance'],
                'closing_balanace'   => $response['remaining_balance'],
                'remarks'           => "Payment for mobile recharge of ₹$amount to $mobile (Operator ID: $operatorId, Circle ID: $circleId)",
            ]);

            $token = CommonHelper::isTokenPresent();
            $apiResponse = $this->sendRequest($endpoint, $payload, $token);

            Cache::forget("profile:{$user->id}:txnStats");


            DB::commit();

            return response()->json([
                'status' => true,
                'data' => $apiResponse,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage(),
            ]);
        }
    }
}
