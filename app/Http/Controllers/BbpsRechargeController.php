<?php

namespace App\Http\Controllers;

use App\Helpers\MobiKwikHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use App\Models\MobikwikToken;

class BbpsRechargeController extends Controller
{
    private  $baseUrl;
    private  $publicKey;
    private  $keyVersion;
    private  $clientId;
    private  $clientSecret;

    public function __construct()
    {
        $this->baseUrl      = config('mobikwik.base_url');
        $this->keyVersion   = config('mobikwik.key_version');
        $this->clientSecret = config('mobikwik.client_secret');
        $this->clientId     = config('mobikwik.client_id');
        // $this->publicKey     = file_get_contents(config('mobikwik.public_key'));
    }

    public function getPlans($operator_id, $circle_id, $plan_type = null)
    {
        try {
            $opId     = $operator_id;
            $cirId    = $circle_id;
            $planType = $plan_type;

            $endpoint = "/recharge/v1/rechargePlansAPI/{$opId}/{$cirId}";

            if (!empty($planType)) {
                $endpoint .= "/{$planType}";
            }

            $response = Http::withoutVerifying()
                ->timeout(120)
                ->retry(3, 3000)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-MClient'    => '14',
                ])
                ->get($this->baseUrl . $endpoint);

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
                'data'    => $data['data']['plans'] ?? [],
            ], 200);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {

            Log::error('Mobikwik Plan API Timeout', [
                'error' => $e->getMessage(),
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

    protected function isTokenPresent()
    {
        try {
            $data =  MobikwikToken::whereDate('created_at', today())->select('token')->first();
            $token = '';
            if (!$data) {
                $mobikwikHelper = new MobiKwikHelper();
                $token = $mobikwikHelper->generateMobikwikToken();
            } else {
                $token = $data->token;
            }
            return $token;
        } catch (\Exception $e) {
            Log::error('Mobikwik Token Present Exception', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
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

            $mobikwikHelper = new MobiKwikHelper();
            $token = $this->isTokenPresent();

            $response = $mobikwikHelper->sendRequest(
                '/recharge/v3/retailerBalance',
                $payload,
                $token
            );

            return $response;
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

            $request->validate([
                'amt'   => 'required|string',
                'cn'   => 'required|string',
                'op' => 'required|string',
                'cir'   => 'required|string',
                'planCode' => 'required|string',
                'adParams' => 'array',
            ]);

            $payload = [
                'amt'      => $request->amt,
                'cn'       => $request->cn,
                'op'       => $request->op,
                'cir'      => $request->cir,
                'planCode' => $request->planCode,
                'adParams' => (object)$request->adParams
            ];

            $mobikwikHelper = new MobiKwikHelper();
            $token = $this->isTokenPresent();

            $response = $mobikwikHelper->sendRequest(
                '/recharge/v3/retailerValidation',
                $payload,
                $token
            );

            return $response;
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

    public function viewBill(Request $request) {}
}
