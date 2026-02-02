<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MobikwikController extends Controller
{
    private $baseUrl;
    private $publicKey;
    private $keyVersion;
    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config("mobikwik.base_url");
        $this->keyVersion = config("mobikwik.key_version");
        $this->clientSecret = config("mobikwik.client_secret");
        $this->clientId = config("mobikwik.client_id");
        $this->publicKey = file_get_contents(config("mobikwik.public_key"));
    }

    public function generateToken()
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    "Content-Type" => "application/json",
                ])
                ->post($this->baseUrl . "/recharge/v1/verify/retailer", [
                    "clientId" => $this->clientId,
                    "clientSecret" => $this->clientSecret,
                ]);

            if (!$response->successful()) {
                Log::error("Mobikwik Token API HTTP Error", [
                    "status" => $response->status(),
                    "response" => $response->body(),
                ]);

                return response()->json(
                    [
                        "success" => false,
                        "message" => "Unable to generate token",
                    ],
                    $response->status()
                );
            }
            $data = $response->json();
            MobikwikToken::create([
                "token" => $data->data->token,
                "creation_time" => now(),
                "response" => $data,
                "created_at" => now(),
                "updated_at" => now(),
            ]);

            return response()->json($data, 200);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Mobikwik Token API Timeout", [
                "error" => $e->getMessage(),
            ]);

            return response()->json(
                [
                    "success" => false,
                    "message" => "Connection timeout, please try again later",
                ],
                504
            );
        } catch (\Exception $e) {
            Log::error("Mobikwik Token API Exception", [
                "error" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return response()->json(
                [
                    "success" => false,
                    "message" => "Internal server error",
                ],
                500
            );
        }
    }

    public function getPlans($provider,$operator_id,$circle_id,$plan_type = null) {
        try {
            $encryptedId = $request->getUser(); 
            $encryptedSecret = $request->getPassword();
            $userData = CommonHelper::validateClient($encryptedId,$encryptedSecret);

            if(!$userData){
                return response()->json([
                    'status'=> false,
                    'message'=> 'you are passing the invalied credentials'
                
                ],403);
            }

            $userId = $userData['user_id'];
            $serviceId = $userData['service_id'];

            $isServiceActive = UserService::where('user_id',$userId)->where('service_id',$serviceId)->where('is_active','1')->first();

            if(!$isServiceActive){
                return response()->json([
                    'status'=> false,
                    'message'=> 'Service is not active at this time',
                ]);

            }



            $opId = $operator_id;
            $cirId = $circle_id;
            $planType = $plan_type;

            switch ($provider) {
                case $provider:
                    try {
                        $endpoint = "/recharge/v1/rechargePlansAPI/{$opId}/{$cirId}";

                        // Append planType ONLY if provided
                        if (!empty($planType)) {
                            $endpoint .= "/{$planType}";
                        }

                        $response = Http::withoutVerifying()
                            ->timeout(120)
                            ->retry(3, 3000)
                            ->withHeaders([
                                "Content-Type" => "application/json",
                                "X-MClient" => "14",
                            ])
                            ->get($this->baseUrl . $endpoint);

                        if (!$response->successful()) {
                            Log::error("Mobikwik Plan API HTTP Error", [
                                "url" => $endpoint,
                                "status" => $response->status(),
                                "response" => $response->body(),
                            ]);

                            return response()->json(
                                [
                                    "success" => false,
                                    "message" =>
                                        "Unable to fetch plans from provider",
                                ],
                                $response->status()
                            );
                        }

                        $data = $response->json();

                        if (
                            isset($data["success"]) &&
                            $data["success"] === false
                        ) {
                            return response()->json(
                                [
                                    "success" => false,
                                    "message" =>
                                        $data["message"]["text"] ??
                                        "Plans not available",
                                    "code" => $data["message"]["code"] ?? null,
                                ],
                                400
                            );
                        }

                        return response()->json(
                            [
                                "success" => true,
                                "data" => $data["data"]["plans"] ?? [],
                            ],
                            200
                        );
                    } catch (\Illuminate\Http\Client\ConnectionException $e) {
                        Log::error("Mobikwik Plan API Timeout", [
                            "error" => $e->getMessage(),
                        ]);

                        return response()->json(
                            [
                                "success" => false,
                                "message" =>
                                    "Provider timeout, please try again later",
                            ],
                            504
                        );
                    } catch (\Exception $e) {
                        Log::error("Mobikwik Plan API Exception", [
                            "error" => $e->getMessage(),
                            "line" => $e->getLine(),
                            "file" => $e->getFile(),
                        ]);

                        return response()->json(
                            [
                                "success" => false,
                                "message" => "Internal server error",
                            ],
                            500
                        );
                    }
                    break;

                default:
                    return response()->json([
                        "status" => false,
                        "message" => "provider slug not found",
                    ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message'=> $e->getMessage(),
            ]);

        }
    }

    public function balance(Request $request,$type)
    {
        switch($type){
            case $type:
            try {
                $request->validate([
                    "memberId" => "required|string",
                ]);

                $payload = [
                    "memberId" => $request->memberId,
                ];

                $mobikwikHelper = new MobiKwikHelper();

                $response = $mobikwikHelper->sendRequest(
                    "/recharge/v3/retailerBalance", // API endpoint
                    $payload, // Payload
                    $request->bearerToken() // Bearer token
                );
            } catch (ConnectionException $e) {
                Log::error("Mobikwik Balance API Timeout", [
                    "error" => $e->getMessage(),
                ]);

                return response()->json(
                    [
                        "success" => false,
                        "message" => "Provider timeout, please try again later",
                    ],
                    504
                );
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => $e->errors(),
                    ],
                    422
                );
            } catch (\Exception $e) {
                Log::error("Mobikwik Balance API Exception", [
                    "error" => $e->getMessage(),
                    "file" => $e->getFile(),
                    "line" => $e->getLine(),
                ]);

                return response()->json(
                    [
                        "success" => false,
                        "message" => "Internal server error",
                    ],
                    500
                );
            }
        }
        
    }

    public function validateRecharge(Request $request)
    {
        try {
            $payload = [
                "amt" => $request->amt,
                "cn" => $request->cn,
                "op" => $request->op,
                "cir" => $request->cir,
                "planCode" => $request->planCode,
                "adParams" => (object) [],
            ];

            return $this->encryptedPost(
                "/recharge/v3/retailerValidation",
                $payload,
                $request->bearerToken()
            );
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function payment(Request $request)
    {
        try {
            $payload = [
                "cn" => $request->cn,
                "op" => $request->op,
                "cir" => $request->cir,
                "amt" => $request->amt,
                "reqid" => $request->reqid,
                "remitterName" => $request->remitterName,
                "paymentRefID" => $request->paymentRefID,
                "paymentMode" => $request->paymentMode,
                "paymentAccountInfo" => $request->paymentAccountInfo,
            ];

            return $this->encryptedPost(
                "/recharge/v3/retailerPayment",
                $payload,
                $request->bearerToken()
            );
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function status(Request $request)
    {
        try {
            $payload = [
                "txId" => $request->txId,
            ];

            $data = $this->encryptedPost(
                "/recharge/v3/retailerStatus",
                $payload,
                $request->bearerToken()
            );

            return response()->json([
                "status" => false,
                "response" => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage(),
            ]);
        }
    }

    public function viewBill(Request $request)
    {
    }
}
