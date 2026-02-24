<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Helpers\MobiKwikHelper;
use App\Models\UserService;
use App\Models\MobikwikToken;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRooting;
use App\Models\DefaultProvider;

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

    protected function ValidateUsers(Request $request)
    {
        try {
            $encryptedId = $request->getUser();
            $encryptedSecret = $request->getPassword();

            $userData = CommonHelper::validateClient($encryptedId, $encryptedSecret);

            if (!$userData) {
                return response()->json([
                    'status' => false,
                    'message' => 'you are passing the invalid credentials'

                ], 403);
            }



            $userId = $userData['user_id'];
            $serviceId = $userData['service'];
            if (empty($userId)) {
                return response()->json([
                    'staus' => false,
                    'message' => 'User Client id is Invailed'
                ]);
            }


            $isServiceActive = UserService::where('user_id', $userId)->where('service_id', $serviceId)->where('is_active', '1')->first();

            // dd($isServiceActive);

            if (!$isServiceActive) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service is not active at this time',
                ]);
            }
            $data = [
                'status' => true,
                'user_id' => $userId,
                'service' => $serviceId,
            ];
            return $data;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getPlans(Request $request, $provider, $circle_id, $operator_id, $plan_type = null)
    {
        try {

            $data = $this->ValidateUsers($request);
            $userId = $data['user_id'];
            $serviceId = $data['service'];
            $ip = $request->ip();

            $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
            if (!$ipWhitelist) {
                return response()->json([
                    'status' => false,
                    'mesage' => 'Ip not whitelisted'
                ]);
            }

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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getBalance(Request $request, $type)
    {

        // $this->ValidateUsers($request);
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        if (!$ipWhitelist) {
            return response()->json([
                'status' => false,
                'mesage' => 'Ip not whitelisted'
            ]);
        }

        switch ($type) {

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
                    if (empty($data)) {
                        $this->generateToken();
                    } else {
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
                        'status' => true,
                        'data' => $response,
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
                    'status' => false,
                    'message' => "Some error occur duing the balance api calls"
                ]);
        }
    }
    protected function isTokenPresent()
    {
        try {
            $tokenData = MobikwikToken::where('expire_at', '>=', now())
                ->orderBy('expire_at', 'desc')
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
            return $token;
        } catch (\Exception $e) {
            Log::error('Mobikwik Token Present Exception', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
        }
    }
    public function validateRecharge(Request $request, $type)
    {
        // $this->ValidateUsers($request);
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        if (!$ipWhitelist) {
            return response()->json([
                'status' => false,
                'mesage' => 'Ip not whitelisted'
            ]);
        }

        $request->validate([
            'amount' => 'required|string',
            'connectionNumber' => 'required',
            'operatorName' => 'required',
            'circleName' => 'required',
            'planCode' => 'required',
            'adParams' => []
        ]);

        switch ($type) {
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
                        'status' => true,
                        'data' => [
                            [
                                'status' => $response['data']['status'],
                                'description' => $response['data']['description'],
                                'balance' => $response['data']['balance'],
                                'discountedPrice' => $response['data']['discountedPrice'],
                                'walletAmount' => $response['data']['walletAmount'],
                                'businessError' => $response['data']['businessError'],
                                'autoPaySupported' => $response['data']['autoPaySupported'],
                                'rewardWidgetEnabled' => $response['data']['rewardWidgetEnabled'],
                                'superCashBurned' => $response['data']['superCashBurned'],
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

    public function mobikwikPayment(Request $request, $type)
    {
        // dd($request->all());
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        if (!$ipWhitelist) {
            return response()->json([
                'status' => false,
                'mesage' => 'Ip not whitelisted'
            ]);
        }

        // $request->validate([
        //     'customerNUmber' => 'required',
        //     'operator' => 'required',
        //     'circle' => 'required',
        //     'amount' => 'required',
        //     'requestId' => 'required',
        //     'customerMobile' => 'required',
        //     'remitterName' => 'required',
        //     'paymentRefID' => 'required',
        //     'paymentMode' => 'required',
        //     'paymentAccountInfo' => 'required',
        //     'additionalPrm1' => 'nullable',
        //     'additionalPrm2' => 'nullable'
        // ]);


        $messages = [
            'customerNUmber.required' => 'Customer number is required.',
            'customerNUmber.string' => 'Customer number must be a valid string.',
            'customerNUmber.regex' => 'Customer number must be a 10-digit number.',

            'operator.required' => 'Operator is required.',
            'operator.exists' => 'Selected operator is invalid.',

            'circle.required' => 'Circle is required.',
            'circle.exists' => 'Selected circle is invalid.',

            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 1.',

            'requestId.required' => 'Request ID is required.',
            'requestId.string' => 'Request ID must be a valid string.',
            'requestId.unique' => 'This Request ID has already been used.',

            'customerMobile.required' => 'Customer mobile number is required.',
            'customerMobile.string' => 'Customer mobile number must be a valid string.',
            'customerMobile.regex' => 'Customer mobile number must be a 10-digit number.',

            'remitterName.required' => 'Remitter name is required.',
            'remitterName.string' => 'Remitter name must be a valid string.',
            'remitterName.max' => 'Remitter name cannot exceed 100 characters.',

            'paymentRefID.required' => 'Payment reference ID is required.',
            'paymentRefID.string' => 'Payment reference ID must be a valid string.',
            'paymentRefID.unique' => 'This Payment Reference ID has already been used.',

            'paymentMode.required' => 'Payment mode is required.',
            'paymentMode.in' => 'Payment mode must be Wallet.',

            'paymentAccountInfo.required' => 'Payment account information is required.',
            'paymentAccountInfo.string' => 'Payment account information must be a valid string.',
            'paymentAccountInfo.max' => 'Payment account information cannot exceed 100 characters.',

            'additionalPrm1.string' => 'Additional parameter 1 must be a valid string.',
            'additionalPrm1.max' => 'Additional parameter 1 cannot exceed 255 characters.',

            'additionalPrm2.string' => 'Additional parameter 2 must be a valid string.',
            'additionalPrm2.max' => 'Additional parameter 2 cannot exceed 255 characters.',
        ];

        $request->validate([
            'customerNUmber' => 'required|string|regex:/^[0-9]{10}$/',
            'operator' => 'required|exists:operators,id',
            'circle' => 'required|exists:circles,id',
            'amount' => 'required|numeric|min:1',
            'requestId' => 'required|string|unique:transactions,request_id',
            'customerMobile' => 'required|string|regex:/^[0-9]{10}$/',
            'remitterName' => 'required|string|max:100',
            'paymentRefID' => 'required|string|unique:transactions,payment_ref_id',
            'paymentMode' => 'required|in:Wallet',
            'paymentAccountInfo' => 'required|string|max:100',
            'additionalPrm1' => 'nullable|string|max:255',
            'additionalPrm2' => 'nullable|string|max:255'
        ], $messages);

        switch ($type) {
            case 'mobikwik-payment':
                try {
                    $connectPeId = CommonHelper::generateConnectPeTransactionId();
                    $defaultSlugData = DefaultProvider::select('provider_slug')->where('service_id', $serviceId)->first();
                    if (empty($defaultSlugData)) {
                        $defaultSlugData = UserRooting::select('provider_slug')->where(['user_id' => $userId, 'service_id' => $serviceId])->first();
                    }

                    $slug = $defaultSlugData->provider_slug;
                    $payload = [
                        "cn" => $request->customerNUmber,
                        "op" => $request->operator,
                        "cir" => $request->circle,
                        "amt" => $request->amount,
                        "reqid" => $request->requestId,
                        "customerMobile" => $request->customerMobile,
                        "remitterName" => $request->remitterName,
                        "paymentRefID" => $request->paymentRefID,
                        "paymentMode" => 'Wallet',
                        "connectpeId" => $connectPeId,
                        "paymentAccountInfo" => '9999999999',
                        'status'                => 'queued',
                        "call"               => 'balance_debit',
                        'user_id'            => $userId,
                        "serviceId"         => $serviceId,
                        'slug'              => $slug,
                    ];


                    Transaction::create([
                        'user_id'               => $userId,
                        'operator_id'           => $payload['op'],
                        'circle_id'             => $payload['cir'],
                        'amount'                => $payload['amt'],
                        'transaction_type'      => $payload['paymentMode'],
                        'request_id'            => $payload['reqid'],
                        'mobile_number'         => $payload['customerMobile'],
                        'payment_ref_id'        => $payload['paymentRefID'],
                        'payment_account_info'  => $payload['paymentAccountInfo'],
                        'recharge_type'         => 'prepaid',
                        'status'                => 'queued',
                        'connectpe_id'          => $connectPeId,
                    ]);

                    $mobikwikHelper = new MobiKwikHelper();
                    $token = $this->isTokenPresent();
                    $endpoint = '/recharge/v3/retailerPayment';

                    dispatch(
                        new DebitBalanceUpdateJob(
                            $endpoint,
                            $payload,
                            $token
                        )
                    )->onQueue('recharge_process_queue');
                    // $response = $mobikwikHelper->sendRequest(
                    //     '/recharge/v3/retailerPayment',
                    //     $payload,
                    //     $token
                    // );
                    // // dd($response);
                    // if ($response['success'] == false) {
                    //     return response()->json([
                    //         'status'  => false,
                    //         'message' => "API Error occurred",
                    //         'error_details' => $response
                    //     ]);
                    // }



                    // $success = $response['success'];
                    // $finalResponse = [
                    //     'success' => $success,
                    //     'status' => $response['data']['status'],
                    //     'txn_id' => $response['data']['txId'],
                    //     'timestamp' => $response['data']['mobikwikstamp'],
                    //     'balance' => $response['data']['balance'],
                    //     'connectpe_id' => $connectPeId

                    // ];
                    return response()->json([
                        'status' => true,
                        'message' => 'Your recharge is queued successfully'

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
                    'status' => false,
                    'message' => "Some error occur while you are doing the payment"
                ]);
        }
    }

    public function fetchPostpaidBill(Request $request, $type)
    {
        // $this->ValidateUsers($request);
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        if (!$ipWhitelist) {
            return response()->json([
                'status' => false,
                'mesage' => 'Ip not whitelisted'
            ]);
        }

        $request->validate([
            'connectionNumber' => 'required|string',
            'operatorId' => 'required|string',
            'circleId' => 'required|string',
            'adParams' => 'nullable'
        ]);

        switch ($type) {
            case 'mobikwik-view-bill':
                $payload = [
                    'cn' => $request->connectionNumber,
                    'op' => $request->operatorId,
                    'cir' => $request->circleId,
                    'adParams' => $request->adParams,

                ];

                $mobikwikHelper = new MobiKwikHelper();
                $token = $this->isTokenPresent();
                $response = $mobikwikHelper->sendRequest(
                    '/recharge/v3/retailerViewbill',
                    $payload,
                    $token
                );

                return response()->json([
                    'status' => false,
                    'data' => $response
                ]);

                break;
            default:
                return response()->json([
                    'status' => false,
                    'message' => 'API Error'
                ]);
        }
    }

    public function mobikwikStatus(Request $request, $type)
    {
        // $this->ValidateUsers($request);
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        if (!$ipWhitelist) {
            return response()->json([
                'status' => false,
                'mesage' => 'Ip not whitelisted'
            ]);
        }

        $request->validate([
            'txnId' => 'required|exists:transactions,request_id',
        ]);

        switch ($type) {

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
                    'status' => false,
                    'message' => 'API Error'
                ]);
        }
    }
}
