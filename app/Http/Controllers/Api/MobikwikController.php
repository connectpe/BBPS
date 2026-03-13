<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CommonHelper;
use App\Helpers\MobiKwikHelper;
use App\Http\Controllers\Controller;
use App\Jobs\DebitBalanceUpdateJob;
use App\Models\DefaultProvider;
use App\Models\MobikwikToken;
use App\Models\Transaction;
use App\Models\UserRooting;
use App\Models\UserService;
use Illuminate\Http\Request;
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
        $this->baseUrl = config('mobikwik.base_url');
        $this->keyVersion = config('mobikwik.key_version');
        $this->clientSecret = config('mobikwik.client_secret');
        $this->clientId = config('mobikwik.client_id');
        $this->publicKey = file_get_contents(config('mobikwik.public_key'));
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

            if (! $response->successful()) {
                Log::error('Mobikwik Token API HTTP Error', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Unable to generate token',
                    ],
                    $response->status()
                );
            }
            $data = $response->json();
            MobikwikToken::create([
                'token' => $data->data->token,
                'creation_time' => now(),
                'response' => $data,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json($data, 200);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Mobikwik Token API Timeout', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Connection timeout, please try again later',
                ],
                504
            );
        } catch (\Exception $e) {
            Log::error('Mobikwik Token API Exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Internal server error',
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
                return [
                    'status' => false,
                    'message' => 'you are passing the invalid credentials',
                ];
            }

            $userId = $userData['user_id'];
            $serviceId = $userData['service'];
            if (empty($userId)) {
                return [
                    'status' => false,
                    'message' => 'User Client id is Invalid',
                ];
            }

            $isServiceActive = UserService::where('user_id', $userId)->where('service_id', $serviceId)->where('is_active', '1')->first();

            // dd($isServiceActive);

            if (! $isServiceActive) {
                return [
                    'status' => false,
                    'message' => 'Service is not active at this time',
                ];
            }
            $data = [
                'status' => true,
                'user_id' => $userId,
                'service' => $serviceId,
            ];

            return $data;
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
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
            // dd($ProviderName);
            switch ($ProviderName) {
                case 'mobikwik-plans':
                    try {
                        $endpoint = "/recharge/v1/rechargePlansAPI/{$opId}/{$cirId}";

                        // Append planType ONLY if provided
                        if (! empty($planType)) {
                            $endpoint .= "/{$planType}";
                        }

                        $response = Http::withoutVerifying()
                            ->timeout(120)
                            ->retry(3, 3000)
                            ->withHeaders([
                                'Content-Type' => 'application/json',
                                'X-MClient' => '14',
                            ])
                            ->get($this->baseUrl . $endpoint);

                        if (! $response->successful()) {
                            Log::error('Mobikwik Plan API HTTP Error', [
                                'url' => $endpoint,
                                'status' => $response->status(),
                                'response' => $response->body(),
                            ]);

                            return response()->json(
                                [
                                    'success' => false,
                                    'message' => 'Unable to fetch plans from provider',
                                ],
                                $response->status()
                            );
                        }

                        $data = $response->json();

                        if (
                            isset($data['success']) &&
                            $data['success'] === false
                        ) {
                            return response()->json(
                                [
                                    'success' => false,
                                    'message' => $data['message']['text'] ??
                                        'Plans not available',
                                    'code' => $data['message']['code'] ?? null,
                                ],
                                400
                            );
                        }
                        $originalData = $data['data']['plans'];

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
                                        'dataBenefit' => $data['dataBenefit'] ?? null,
                                        'isPopular' => $data['isPopular'] ?? null,
                                        'stringTalktime' => $data['stringTalktime'] ?? null,
                                        'validityInDays' => $data['validityInDays'] ?? null,

                                    ];
                                }, $originalData ?? []),
                            ],
                            200
                        );
                    } catch (\Illuminate\Http\Client\ConnectionException $e) {
                        Log::error('Mobikwik Plan API Timeout', [
                            'error' => $e->getMessage(),
                        ]);

                        return response()->json(
                            [
                                'success' => false,
                                'message' => 'Provider timeout, please try again later',
                            ],
                            504
                        );
                    } catch (\Exception $e) {
                        Log::error('Mobikwik Plan API Exception', [
                            'error' => $e->getMessage(),
                            'line' => $e->getLine(),
                            'file' => $e->getFile(),
                        ]);

                        return response()->json(
                            [
                                'success' => false,
                                'message' => 'Internal server error',
                            ],
                            500
                        );
                    }
                    break;

                default:
                    return response()->json([
                        'status' => false,
                        'message' => 'provider slug not found',
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
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        if (! $ipWhitelist) {
            return response()->json([
                'status' => false,
                'mesage' => 'Ip not whitelisted',
            ]);
        }

        switch ($type) {

            case 'mobikwik-balance':
                try {
                    $request->validate([
                        'memberId' => 'required|string',
                    ]);

                    $payload = [
                        'memberId' => $request->memberId,
                    ];

                    $mobikwikHelper = new MobiKwikHelper;

                    $data = MobikwikToken::whereDate('creation_time', today())->select('token')->first();
                    // dd($data);
                    $token = '';
                    if (empty($data)) {
                        $this->generateToken();
                    } else {
                        $token = $data->token;
                    }

                    $endpoint = '/recharge/v3/retailerBalance';

                    $response = $mobikwikHelper->sendRequest(
                        $endpoint,
                        $payload,
                        $token
                    );
                    // dd($response);

                    if (!$response->successfull()) {
                        Log::error('Mobikwik Balance API HTTP Error', [
                            'url' => $endpoint,
                            'status' => $response->status(),
                            'response' => $response->body(),
                        ]);
                        return response()->json([
                            'status' => false,
                            'message' => 'Unable to fetch response from provider side'
                        ]);
                    }

                    if (
                        isset($response['success']) &&
                        $response['success'] === false
                    ) {
                        return response()->json(
                            [
                                'success' => false,
                                'message' => $response['message']['text'] ??
                                    'Token is expired/Invalid Token/Token not found in request',
                                'code' => $response['message']['code'] ?? null,
                            ],
                            400
                        );
                    }

                    return response()->json([
                        'status' => true,
                        'data' => $response,
                    ]);
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    Log::error('Mobikwik Balance API Timeout', [
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Provider timeout, please try again later',
                        ],
                        504
                    );
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => $e->errors(),
                        ],
                        422
                    );
                } catch (\Exception $e) {
                    Log::error('Mobikwik Balance API Exception', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);

                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Internal server error',
                        ],
                        500
                    );
                }
                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Some error occur duing the balance api calls',
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
            if (! $tokenData) {
                $mobikwikHelper = new MobiKwikHelper;
                $data = $mobikwikHelper->generateMobikwikToken();
                // dd($data);
                $token = $data->token;
            } else {
                $token = $tokenData->token;
            }

            return $token;
        } catch (\Exception $e) {
            Log::error('Mobikwik Token Present Exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    public function validateRecharge(Request $request, $type)
    {
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        if (! $ipWhitelist) {
            return response()->json([
                'status' => false,
                'mesage' => 'Ip not whitelisted',
            ]);
        }

        $request->validate([
            'amount' => 'required|string',
            'connectionNumber' => 'required',
            'operatorId' => 'required',
            'circleId' => 'required',
            'planCode' => 'required',
            'adParams' => [],
        ]);

        switch ($type) {
            case 'mobiwik-recharge-validation':
                try {
                    $payload = [
                        'amt' => $request->amount,
                        'cn' => $request->connectionNumber,
                        'op' => $request->operatorId,
                        'cir' => $request->circleId,
                        'planCode' => $request->planCode,
                        'adParams' => (object) [],
                    ];

                    $mobikwikHelper = new MobiKwikHelper;
                    $token = $this->isTokenPresent();

                    $endpoint = '/recharge/v3/retailerValidation';

                    $response = $mobikwikHelper->sendRequest(
                        $endpoint,
                        $payload,
                        $token
                    );

                    if (!$response->successfull()) {
                        Log::error('Mobikwik Validation API HTTP Error', [
                            'url' => $endpoint,
                            'status' => $response->status(),
                            'response' => $response->body(),
                        ]);
                        return response()->json([
                            'status' => false,
                            'message' => 'Unable to fetch validation response from provider side'
                        ]);
                    }

                    if (
                        isset($response['success']) &&
                        $response['success'] === false
                    ) {
                        return response()->json(
                            [
                                'success' => false,
                                'message' => $response['message']['text'] ??
                                    'Invalid Hash Value',
                                'code' => $response['message']['code'] ?? null,
                                'data' => $response['data']
                            ],
                            400
                        );
                    }

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
                            ],
                        ],
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                    ]);
                }
        }
    }

    public function mobikwikPayment(Request $request, $type)
    {
        // dd($type);
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();
        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        // if (!$ipWhitelist) {
        //     return response()->json([
        //         'status' => false,
        //         'mesage' => 'Ip not whitelisted'
        //     ]);
        // }

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
            'connectionNUmber.required' => 'Customer number is required.',
            'connectionNUmber.string' => 'Customer number must be a valid string.',
            'connectionNUmber.regex' => 'Customer number must be a 10-digit number.',

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

            // 'additionalPrm2.string' => 'Additional parameter 2 must be a valid string.',
            // 'additionalPrm2.max' => 'Additional parameter 2 cannot exceed 255 characters.',
        ];

        $request->validate([
            'connectionNUmber' => 'required|string|regex:/^[0-9]{10}$/',
            'operator' => 'required',
            'circle' => '',
            'amount' => 'required|numeric|min:1',
            'requestId' => 'required|string|unique:transactions,request_id',
            'customerMobile' => 'required|string|regex:/^[0-9]{10}$/',
            'agentId' => 'required|string',
            'remitterName' => 'required|string|max:100',
            'paymentRefID' => 'required|string|unique:transactions,payment_ref_id',
            'paymentMode' => 'required|string',
            'paymentAccountInfo' => 'required|string|max:100',
            // 'additionalPrm1' => 'nullable|string|max:255',
            // 'additionalPrm2' => 'nullable|string|max:255',
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
                        'cn' => $request->connectionNUmber,
                        'op' => $request->operator,
                        "cir" => $request->circle,
                        'amt' => $request->amount,
                        'reqid' => $request->requestId,
                        'customerMobile' => $request->customerMobile,
                        'agentId' => $request->agentId,
                        'remitterName' => $request->remitterName,
                        'paymentRefID' => $request->paymentRefID,
                        'paymentMode' => $request->paymentMode,
                        // "connectpeId" => $connectPeId,
                        'paymentAccountInfo' => $request->paymentAccountInfo,
                        // "bankCode" => "ICIC",
                        // 'ad9' => $request->additionalPrm1,
                        // 'ad3' => $request->additionalPrm2,
                        // 'status'                => 'queued',
                        // "call"               => 'balance_debit',
                        // 'user_id'            => $userId,
                        // "serviceId"         => $serviceId,
                        // 'slug'              => $slug,
                    ];

                    // dd($payload);
                    // Transaction::create([
                    //     'user_id'               => $userId,
                    //     'operator_id'           => $payload['op'],
                    //     'circle_id'             => $payload['cir'],
                    //     'amount'                => $payload['amt'],
                    //     'transaction_type'      => $payload['paymentMode'],
                    //     'request_id'            => $payload['reqid'],
                    //     'mobile_number'         => $payload['customerMobile'],
                    //     'payment_ref_id'        => $payload['paymentRefID'],
                    //     'payment_account_info'  => $payload['paymentAccountInfo'],
                    //     'recharge_type'         => 'prepaid',
                    //     'status'                => 'queued',
                    //     'connectpe_id'          => $connectPeId,
                    // ]);

                    $mobikwikHelper = new MobiKwikHelper;
                    $token = $this->isTokenPresent();
                    $endpoint = '/recharge/v3/retailerPayment';

                    // dd($token);

                    $response = $mobikwikHelper->sendRequest(
                        $endpoint,
                        $payload,
                        $token
                    );

                    // dd($response);
                    return response()->json([
                        'status' => true,
                        'data' => $response,
                    ]);

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
                        'message' => 'Your recharge is queued successfully',

                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                    ]);
                }

                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Some error occur while you are doing the payment',
                ]);
        }
    }

    public function fetchPostpaidBill(Request $request, $type)
    {
        // dd($request->all());
        // $this->ValidateUsers($request);
        $data = $this->ValidateUsers($request);
        $userId = $data['user_id'];
        $serviceId = $data['service'];
        $ip = $request->ip();

        $ipWhitelist = CommonHelper::checkIpWhiteList($userId, $serviceId, $ip);
        // if (!$ipWhitelist) {
        //     return response()->json([
        //         'status' => false,
        //         'mesage' => 'Ip not whitelisted'
        //     ]);
        // }

        $request->validate([
            'connectionNumber' => 'required|string',
            'operatorId' => 'required|string',
            'circleId' => 'nullable|string',
            'adParams' => 'nullable',
        ]);

        switch ($type) {
            case 'mobikwik-view-bill':
                $payload = [
                    'cn' => $request->connectionNumber,
                    'op' => $request->operatorId,
                    'cir' => $request->circleId ?? '',
                    'agentId' => "MK01MK01INB523643654",
                    'adParams' => (object)[],
                ];
                // dd($payload);
                $mobikwikHelper = new MobiKwikHelper;
                $token = $this->isTokenPresent();
                // dd($token);
                if (! $token) {
                    return response()->json([
                        'status' => false,
                        'message' => 'token not found',
                    ]);
                }
                $response = $mobikwikHelper->sendRequest(
                    '/recharge/v3/retailerViewbill',
                    $payload,
                    $token
                );

                return response()->json([
                    'status' => true,
                    'data' => $response,
                ]);

                break;
            default:
                return response()->json([
                    'status' => false,
                    'message' => 'API Error',
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
        // if (!$ipWhitelist) {
        //     return response()->json([
        //         'status' => false,
        //         'mesage' => 'Ip not whitelisted'
        //     ]);
        // }

        $request->validate([
            // 'txnId' => 'required|exists:transactions,request_id',
            'txnId' => 'required|string',
        ]);

        switch ($type) {

            case 'mobikwik-status':
                try {
                    $payload = [
                        'txId' => $request->txnId,
                    ];

                    $mobikwikHelper = new MobiKwikHelper;
                    $token = $this->isTokenPresent();
                    // dd($token);

                    $data = $mobikwikHelper->sendRequest(
                        '/recharge/v3/retailerStatus',
                        $payload,
                        $token
                    );

                    return response()->json([
                        'status' => false,
                        'response' => $data,
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                    ]);
                }

                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'API Error',
                ]);
        }
    }
}
