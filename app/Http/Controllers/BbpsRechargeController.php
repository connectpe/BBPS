<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BbpsRechargeController extends Controller
{
    private  $baseUrl;
    private  $publicKey;
    private  $keyVersion;
    private  $clientId;
    private  $clientSecret;

    public function __construct()
    {
        $this->baseUrl   = 'https://alpha3.mobikwik.com';
        // $this->publicKey = file_get_contents(storage_path('keys/bbps_public_key.pem'));
        $this->keyVersion = '1.0';
        $this->clientSecret = '';
        $this->clientId = '';
    }

    public function generateToken()
    {
        try {
            $response = Http::timeout(15)->post(
                $this->baseUrl . '/recharge/v1/verify/retailer',
                [
                    'clientId'     => $this->clientId,
                    'clientSecret' => $this->clientSecret,
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

            return response()->json($response->json(), 200);
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
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    public function getPlans(Request $request)
    {
        $request->validate([
            'mobile'      => 'required|string',
            'operator_id' => 'required|integer',
            'circle_id'   => 'required|integer',
            'plan_type'   => 'nullable|integer',
        ]);

        try {
            $opId      = $request->operator_id;
            $cirId     = $request->circle_id;
            $planType  = $request->plan_type;


            $endpoint = "/recharge/v1/rechargePlansAPI/{$opId}/{$cirId}/{$planType}";

            // if (!empty($planType)) {
            //     $endpoint .= "/{$planType}";
            // }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-MClient'    => '14',
            ])->timeout(20)
                ->get(config('services.mobikwik.base_url') . $endpoint);


            if (!$response->successful()) {
                Log::error('Mobikwik Plan API HTTP Error', [
                    'url'      => $endpoint,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to fetch plans from provider',
                ], $response->status());
            }

            $data = $response->json();


            if (isset($data['success']) && $data['success'] === false) {
                return response()->json([
                    'success' => false,
                    'message' => $data['message']['text'] ?? 'Plans not available',
                    'code'    => $data['message']['code'] ?? null,
                ], 400);
            }


            return response()->json([
                'success' => true,
                'data'    => $data['data'] ?? [],
            ], 200);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            Log::error('Mobikwik Plan API Timeout', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Provider timeout, please try again later',
            ], 504);
        } catch (\Exception $e) {

            Log::error('Mobikwik Plan API Exception', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    public function balance(Request $request)
    {
        try {
            $request->validate([
                'memberId' => 'required|string',
            ]);

            $payload = [
                'memberId' => $request->memberId,
            ];

            return $this->encryptedPost(
                '/recharge/v3/retailerBalance',
                $payload,
                $request->bearerToken()
            );
        } catch (ConnectionException $e) {

            Log::error('Mobikwik Balance API Timeout', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Provider timeout, please try again later',
            ], 504);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            Log::error('Mobikwik Balance API Exception', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    public function validateRecharge(Request $request)
    {
        try {
            $payload = [
                'amt'      => $request->amt,
                'cn'       => $request->cn,
                'op'       => $request->op,
                'cir'      => $request->cir,
                'planCode' => $request->planCode,
                'adParams' => (object)[]
            ];

            return $this->encryptedPost(
                '/recharge/v3/retailerValidation',
                $payload,
                $request->bearerToken()
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function payment(Request $request)
    {
        try {
            $payload = [
                'cn'                  => $request->cn,
                'op'                  => $request->op,
                'cir'                 => $request->cir,
                'amt'                 => $request->amt,
                'reqid'               => $request->reqid,
                'remitterName'        => $request->remitterName,
                'paymentRefID'        => $request->paymentRefID,
                'paymentMode'         => $request->paymentMode,
                'paymentAccountInfo'  => $request->paymentAccountInfo,
            ];

            return $this->encryptedPost(
                '/recharge/v3/retailerPayment',
                $payload,
                $request->bearerToken()
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function status(Request $request)
    {
        try {
            $payload = [
                'txId' => $request->txId
            ];

            $data =  $this->encryptedPost(
                '/recharge/v3/retailerStatus',
                $payload,
                $request->bearerToken()
            );

            return response()->json([
                'status' => false,
                'response' => $data

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
