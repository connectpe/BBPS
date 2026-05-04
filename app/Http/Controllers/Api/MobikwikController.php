<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CommonHelper;
use App\Helpers\MobiKwikHelper;
use App\Helpers\TransactionHelper;
use App\Helpers\ApiResponseHelper;
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
use App\Validation\RetailerPaymentValidation;
use App\Jobs\RechargeDebitBalanceAndStatusUpdateJob;

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

    protected function ValidateUsers(Request $request)
    {
        try {
            // dd($request->all());
            $encryptedId = $request->getUser();
            $encryptedSecret = $request->getPassword();
            // dd($encryptedSecret);
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

    public function getPlans(Request $request, $circle_id, $operator_id, $plan_type = null)
    {
        try {

            $data = $this->ValidateUsers($request);
            // dd($data);
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

            $providerSlug = CommonHelper::getProviderSlug($userId, $serviceId);

            $opId = $operator_id;
            $cirId = $circle_id;
            $planType = $plan_type;

            switch ($providerSlug['provider_slug']) {
                case 'mobikwik':
                    try {
                        $endpoint = "/recharge/v1/rechargePlansAPI/{$opId}/{$cirId}";
                        // dd($endpoint);
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

    public function getBalance(Request $request)
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

        $providerSlug = CommonHelper::getProviderSlug($userId, $serviceId);

        switch ($providerSlug['provider_slug']) {

            case 'mobikwik':
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
                        $mobikwikHelper->generateToken();
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

    public function validateRecharge(Request $request)
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

        $providerSlug = CommonHelper::getProviderSlug($userId, $serviceId);

        $request->validate([
            'amount' => 'required|string',
            'connectionNumber' => 'required',
            'operatorId' => 'required',
            'circleId' => 'required',
            'planCode' => 'required',
            'adParams' => [],
        ]);

        switch ($providerSlug['provider_slug']) {
            case 'mobikwik':
                try {
                    $payload = [
                        'amt' => $request->amount,
                        'cn' => $request->connectionNumber,
                        'op' => $request->operatorId,
                        'cir' => $request->circleId,
                        'planCode' => $request->planCode,
                        'adParams' => (object) [],
                    ];
                    // dd($payload);
                    $mobikwikHelper = new MobiKwikHelper;
                    $token = $mobikwikHelper->isTokenPresent();
                    // dd($token);

                    $endpoint = '/recharge/v3/retailerValidation';

                    $response = $mobikwikHelper->sendRequest(
                        $endpoint,
                        $payload,
                        $token
                    );

                    if (!isset($response['success'])) {
                        Log::error('Mobikwik Validation API Invalid Response', [
                            'url' => $endpoint,
                            'response' => $response,
                        ]);

                        return response()->json([
                            'status' => false,
                            'message' => 'Invalid response from provider'
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

    public function retailerPayment(Request $request)
    {
        $agent = [
            'ip'        => $headers['cf-connecting-ip'][0] ?? $request->ip(),
            'userAgent' => $headers['user-agent'][0] ?? $request->header('User-Agent')
        ];

        $userId = $request['auth_data']['user_id'];
        $serviceId = $request['auth_data']['service_id'];

        $provider = CommonHelper::getProviderSlug($userId, $serviceId);
        $providerSlug = $provider['provider_slug'];

        $validator = RetailerPaymentValidation::validate($request->all());

        if ($validator->fails()) {
            return ApiResponseHelper::missing($validator->errors()->first());
        }

        switch ($providerSlug) {
            case 'mobikwik':
                try {

                    $connectPeId = CommonHelper::generateConnectPeTransactionId();

                    $payload = [
                        'cn' => $request->connectionNumber,
                        'op' => $request->operator,
                        'cir' => $request->circle,
                        'amt' => $request->amount,
                        'reqid' => $request->requestId,
                        'customerMobile' => $request->customerMobile,
                        'agentId' => $request->agentId,
                        'remitterName' => $request->remitterName,
                        'paymentRefID' => $request->paymentRefID,
                        'paymentMode' => $request->paymentMode,
                        'paymentAccountInfo' => $request->paymentAccountInfo,
                    ];

                    $connectpeId = CommonHelper::generateConnectPeTransactionId();

                    $rechargeOrderCreate = TransactionHelper::createRechargeTransactionOrders($userId, $serviceId, $connectpeId, $payload, $agent);

                    if (!$rechargeOrderCreate['status']) {
                        return ApiResponseHelper::failed($rechargeOrderCreate['message'], []);
                    }

                    // Dispatch Job
                    dispatch(new RechargeDebitBalanceAndStatusUpdateJob($connectpeId, $userId, 'balance_debit', $serviceId, $payload, '', '', ''))->onQueue('recharge_debit_queue');

                    // Response
                    return ApiResponseHelper::success(
                        'Recharge order accepted successfully',
                        [
                            'paymentRefID' => $request->paymentRefID,
                            'connectpeID'  => $connectpeId,
                            'status'      => 'queue'
                        ],
                        200
                    );

                    dd($rechargeOrderCreate);

                    $mobikwikHelper = new MobiKwikHelper;
                    $token = $mobikwikHelper->isTokenPresent();
                    $endpoint = '/recharge/v3/retailerPayment';


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

                    // dispatch(
                    //     new RechargeDebitBalanceAndStatusUpdateJob(
                    //         $endpoint,
                    //         $payload,
                    //         $token
                    //     )
                    // )->onQueue('recharge_process_queue');
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
                    // return response()->json([
                    //     'status' => true,
                    //     'message' => 'Your recharge is queued successfully',

                    // ]);
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
                $token = $mobikwikHelper->isTokenPresent();
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
                    $token = $mobikwikHelper->isTokenPresent();
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
