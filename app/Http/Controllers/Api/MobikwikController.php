<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Helpers\MobiKwikHelper;
use App\Models\UserService;
use App\Models\MobikwikToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

    protected function ValidateUsers(Request $request){
        try{
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
            $serviceId = $userData['service'];
            if(empty($userId)){
                return response()->json([
                    'staus'=> false,
                    'message'=> 'User Client id is Invailed'
                ]);
            }


            $isServiceActive = UserService::where('user_id',$userId)->where('service_id',$serviceId)->where('is_active','1')->first();

            // dd($isServiceActive);

            if(!$isServiceActive){
                return response()->json([
                    'status'=> false,
                    'message'=> 'Service is not active at this time',
                ]);

            }

        }catch(Exception $e){
            return respponse()->json([
                'status'=> false,
                'message'=> $e->getMessage()
            ]);
        }
    }
    public function getPlans(Request $request,$provider,$circle_id,$operator_id,$plan_type = null) {
        try {

            $this->ValidateUsers($request);
            $opId = $operator_id;
            $cirId = $circle_id;
            $planType = $plan_type;
            $ProviderName = $provider;
            switch ($ProviderName) {
                case 'plans':
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
                        $originalData = $data["data"]["plans"];
                        // dd($originalData);
                        return response()->json(
                        [
                            'success' => true,
                            'data' => array_map(function ($data) {
                                return [
                                    'operatorId' => $data['operatorId'] ?? null,
                                    'circleId' => $data['circleId'] ?? null,
                                    'planType' => $data['planType'] ?? null,
                                    'planCode' => $data['planCode'] ?? null,
                                    'amount' => $data['amount'] ?? null,
                                    'talktime' => $data['talktime'] ?? null,
                                    'validity' => $data['validity'] ?? null,
                                    'planName' => $data['planName'] ?? null,
                                    'planDescription' => $data['planDescription'] ?? null,
                                    'isPopular' => $data['isPopular'] ?? null,
                                    'stringTalktime' => $data['stringTalktime'] ?? null,
                                    'validityInDays' => $data['validityInDays'] ?? null,

                                ];
                            }, $originalData ?? []),
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

    public function getBalance(Request $request,$type)
    {
        
            $this->ValidateUsers($request);

            switch($type){

            case 'mobikwik-balance':
            try {
                $request->validate([
                    "memberId" => "required|string",
                ]);

                $payload = [
                    "memberId" => $request->memberId,
                ];

                $mobikwikHelper = new MobiKwikHelper();


                $data = MobikwikToken::whereDate('creation_time', today())->select('token')->first();
                // dd($data);
                $token = '';
                if(empty($data)){
                    $this->generateToken();
                }else{
                    $token = $data->token;
                }

                $response = $mobikwikHelper->sendRequest(
                    "/recharge/v3/retailerBalance", 
                    $payload, 
                    $token
                );
                // dd($response);

                // if(!$response->successfull()){
                //     return response()->json([
                //         'status'=> false,
                //         'message'=> 'invailed response'
                //     ]);
                // }

                return response()->json([
                    'status'=> true,
                    'data'=> $response,
                ]);
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
            break;

            default:
                return response()->json([
                    'status'=> false,
                    'message'=> "Some error occur duing the balance api calls"
                ]);
        }

       
        
    }
    protected function isTokenPresent()
    {
        try {
            $tokenData = MobikwikToken::where('expire_at', '>=', now()) 
                        ->select('token', 'expire_at')
                        ->first();
                       
            $token = null;
            if (!$tokenData) {
                $mobikwikHelper = new MobiKwikHelper();
                $data = $mobikwikHelper->generateMobikwikToken();
                $token = $data->token;
                
            } else {
                $token = $tokenData->token;
            }
            // dd($token);
            return $token;
        } catch (\Exception $e) {
            Log::error('Mobikwik Token Present Exception', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
        }
    }
    public function validateRecharge(Request $request,$type)
    {
        $this->ValidateUsers($request);

        $request->validate([
            'amount'=> 'required|string',
            'connectionNumber'=> 'required',
            'operatorName'=> 'required',
            'circleName'=> 'required',
            'planCode'=> 'required',
            'adParams'=> []
        ]);

        switch($type){
            case "mobiwik-recharge-validation":
                    try {
                $payload = [
                    "amt" => $request->amount,
                    "cn" => $request->connectionNumber,
                    "op" => $request->operatorName,
                    "cir" => $request->circleName,
                    "planCode" => $request->planCode,
                    "adParams" => (object) [],
                ];

            $mobikwikHelper = new MobiKwikHelper();
            $token = $this->isTokenPresent();

            $response = $mobikwikHelper->sendRequest(
                '/recharge/v3/retailerValidation',
                $payload,
                $token
            );
                
            return response()->json([
                'status'=> true,
                'data'=> [
                    [
                        'status'=> $response['data']['status'],
                        'description'=>$response['data']['description'],
                        'balance'=>$response['data']['balance'],
                        'discountedPrice'=>$response['data']['discountedPrice'],
                        'walletAmount'=>$response['data']['walletAmount'],
                        'businessError'=>$response['data']['businessError'],
                        'autoPaySupported'=>$response['data']['autoPaySupported'],
                        'rewardWidgetEnabled'=>$response['data']['rewardWidgetEnabled'],
                        'superCashBurned'=>$response['data']['superCashBurned'],
                    ]
                ]
            ]);
            } catch (\Exception $e) {
                return response()->json([
                    "status" => false,
                    "message" => $e->getMessage(),
                ]);
            }
        }

        
    }

    public function mobikwikPayment(Request $request,$type)
    {
        $this->ValidateUsers($request);
        $request->validate([
            'customerNUmber'=> 'required',
            'operator'=> 'required',
            'circle'=> 'required',
            'amount'=> 'required',
            'requestId'=> 'required',
            'customerMobile'=>'required',
            'remitterName'=> 'required',
            'paymentRefID'=> 'required',
            'paymentMode'=> 'required',
            'paymentAccountInfo'=> 'required',
            'additionalPrm1'=>'nullable',
            'additionalPrm2'=>'nullable'
        ]);

        switch($type){
            case 'mobikwik-payment':
                try {
                    $payload = [
                        "cn" => $request->customerNUmber,
                        "op" => $request->operator,
                        "cir" => $request->circle,
                        "amt" => $request->amount,
                        "reqid" => $request->requestId,
                        "customerMobile"=>$request->customerMobile,
                        "remitterName" => $request->remitterName,
                        "paymentRefID" => $request->paymentRefID,
                        "paymentMode" => 'Wallet',
                        "paymentAccountInfo" => '9999999999',
                    ];
                    $mobikwikHelper = new MobiKwikHelper();
                    $token = $this->isTokenPresent();
                    $response = $mobikwikHelper->sendRequest(
                        '/recharge/v3/retailerPayment',
                        $payload,
                        $token
                    );
                    if (!isset($response['status']) || $response['status'] !== 'SUCCESS') {
                        return response()->json([
                            'status'  => false,
                            'message' => "API Error occurred",
                            'error_details' => $response 
                        ]);
                    }
                    $connectPeId = "CPE".time().rand(1000000,9999999);
                    return response()->json([
                        'status'=> true,
                        'data'=> $response,
                        'connectRefId'=>$connectPeId
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        "status" => false,
                        "message" => $e->getMessage(),
                    ]);
                }



            break;

            default:
                return response()->json([
                    'status'=> false,
                    'message'=> "Some error occur while you are doing the payment"
                ]);
        }
        
    }

    public function fetchPostpaidBill(Request $request,$type){
        $this->ValidateUsers($request);

        $request->validate([
            'connectionNumber'=> 'required|string',
            'operatorId'=> 'required|string',
            'circleId'=> 'required|string',
            'adParams'=> 'nullable'
        ]);

        switch($type){
            case 'mobikwik-view-bill':
                $payload = [
                    'cn'=> $request->connectionNumber,
                    'op'=> $request->operatorId,
                    'cir'=> $request->circleId,
                    'adParams'=> $request->adParams,

                ];

                $mobikwikHelper = new MobiKwikHelper();
                $token = $this->isTokenPresent();
                dd($token);
                

                $response = $mobikwikHelper->sendRequest(
                    '/recharge/v3/retailerViewbill',
                    $payload,
                    $token
                );

                return response()->json([
                    'status'=> false,
                    'data'=> $response
                ]);

            break;
            default:
                return response()->json([
                    'status'=> false,
                    'message'=> 'API Error'
                ]);
        }
        
    }

    public function mobikwikStatus(Request $request,$type)
    {
        $this->ValidateUsers($request);
        $request->validate([
            'txnId'=> 'required|string',
        ]);

        switch($type){

            case 'mobikwik-status':
                try {
                    $payload = [
                        "txId" => $request->txId,
                    ];

                    $mobikwikHelper = new MobiKwikHelper();
                    $token = $this->isTokenPresent();
                    // dd($token);

                    $data = $mobikwikHelper->sendRequest(
                        "/recharge/v3/retailerStatus",
                        $payload,
                        $token
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
                
            break;

            default:
                return response()->json([
                    'status'=> false,
                    'message'=> 'API Error'
                ]);
        }
        
    }

    
}