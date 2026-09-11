<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;
use App\Models\User;
use App\Models\BusinessInfo;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Helpers\SeamlessPayinHelper;
use App\Helpers\TransactionHelper;
use App\Models\SeamlessUpiCollection;
use App\Models\Transaction;
use Illuminate\Validation\Rule;
use Exception;
use Illuminate\Support\Facades\Validator;

class PayinOrdersController extends Controller
{

    protected $cashfreePayinUrl;
    private $cashfreeappid;
    private $cashfreesecretkey;
    private $cashfreeapiversion;
    private $easebuzzBaseUrl;

    public function __construct()
    {
        $this->cashfreePayinUrl = config('payin.cashfree_url');
        $this->cashfreeappid = config('payin.cashfree_app_id');
        $this->cashfreesecretkey = config('payin.cashfree_secret_key');
        $this->cashfreeapiversion = config('payin.cashfree_api_version');
        $this->easebuzzBaseUrl = config('payin.easebuzz_base_url');
    }



    public function createOrders(Request $request)
    {

        try {

            $userIdAndServiceId = CommonHelper::getUserIdAndServiceIdUsingKeyAndSecret($request->header());

            $userId = $userIdAndServiceId['user_id'] ?? null;
            $serviceId = $userIdAndServiceId['service_id'] ?? null;

            $activeUser = User::where('id', $userId)->where('status', '1')->first();
            $isKyc = BusinessInfo::where('user_id', $userId)->where('is_kyc', '1')->first();

            if (!$activeUser) {
                throw new Exception("Your are inactive user, Please contact to the administrator");
            }

            if (!$isKyc) {
                throw new Exception("KYC not completed");
            }

            CommonHelper::isUserServiceActiveUsingUserIdAndServiceId($userId, $serviceId);
            CommonHelper::isGlobalServiceActive($serviceId);
            $getProviderSlug = CommonHelper::getProviderSlug($userId, $serviceId);
            $providerSlug = $getProviderSlug['provider_slug'] ?? null;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error : ' . $e->getMessage()
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'mobile_number' => 'required|digits:10',

            'transaction_id' => [
                'required',
                'string',
                'min:6',
                'max:100',
                Rule::unique('seamless_upi_collections', 'cust_txn_id'),
            ],
        ]);

        switch ($providerSlug) {
            case 'cgpey':
                try {
                    $rules = [
                        "name" => ["required", "max:100", "regex:/^[A-Za-zÀ-ÿ]{2,30}(\s+[A-Za-zÀ-ÿ]{2,30})+$/"],
                        'mobile_number' => 'required|digits:10',
                        'amount' => 'required|numeric|min:100',
                        'transaction_id' => 'required|string|max:100|unique:kavach_payins,client_txn_id',
                    ];
                    $messages = ['name.regex' => 'Please Enter a valid full name (Only Indian names, Indian characters, spaces, and dots allowed).'];
                    // if ($userId == '554') {
                    //     $rules['pan'] = 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i';
                    // }

                    $request->validate($rules, $messages);

                    // $url = $this->cgpeyPayinUrl;


                    $payload = [
                        'name' => $request->name,
                        'mobile_number' => $request->mobile_number,
                        'transaction_id' => $request->transaction_id,
                        'amount' => $request->amount,
                    ];

                    $response = Http::withHeaders([
                        'x-api-key'    => $this->apikey,
                        'x-secret-key' => $this->secretkey,
                        'ip-address'   => $this->ip,
                        'Content-Type' => 'application/json',
                    ])
                        ->timeout(40)
                        ->connectTimeout(30)
                        ->retry(
                            3,
                            2000,
                            function ($exception, $request) {

                                return $exception instanceof ConnectionException;
                            }
                        )
                        ->post($url, $payload);



                    $result = $response->json();

                    $alldata = TransactionHelper::payinFeeTaxDeduction($userId, $request->amount, $serviceId);

                    if ($response->successful()) {

                        $upiUrl = $result['data']['intentData'];

                        $orderID = $this->generateOrderId();

                        parse_str(parse_url($upiUrl, PHP_URL_QUERY), $params);

                        $pa = $params['pa'] ?? null;
                        \DB::table('kavach_payins')->insert([
                            'cust_name' => $request->name,
                            'cust_mobile' => $request->mobile_number,
                            'client_txn_id' =>  $request->transaction_id,
                            'amount' => $request->amount,
                            'fee' => $alldata['fee'],
                            'tax' => $alldata['tax'],
                            'net_amount' => $alldata['netAmount'],

                            'cust_email' => $data->email,
                            'user_id' => $data->id,
                            'txn_id' => $result['data']['txnId'],
                            'txn_order_id' => $orderID,
                            'status' => $result['data']['status'],
                            'type'  => $type,
                            'root' => $pa,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        return response()->json([
                            'status' => true,
                            'message' => 'Payment initiated successfully',
                            'data' => [
                                'amount' => $result['data']['amount'],
                                'message' => $result['data']['statusDesc'],
                                'orderid' => $result['data']['clientRefId'],
                                'payment_link' => $result['data']['intentData'],
                                'txnid' => $orderID
                            ]
                        ]);
                    } else {
                        return response()->json([
                            'message' => 'API ERROR',
                            'status' => false,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('CGPEY Payin Error', ['error' => $e->getMessage()]);
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                    ], 500);
                }

                break;

            case 'cashfree':
                // Implement Cashfree payment logic here
                $request->validate([
                    'name' => 'required|string|max:100',
                    'email' => 'required|email|max:100',
                    'mobileNumber' => 'required|digits:10',
                    'amount' => 'required|numeric|min:100',
                    'cust_txn_id' => 'required|string|max:100|unique:upi_collections,cust_txn_id',
                ]);

                $url = $this->cashfreePayinUrl;

                $payload = [
                    "customer_details" => [
                        "customer_name"  => $request->name,
                        "customer_email" => $request->email,
                        "customer_phone" => $request->mobileNumber,
                    ],

                    "link_amount" => (float) $request->amount,
                    "link_currency" => "INR",
                    "link_id" => $request->cust_txn_id,
                    "link_purpose" => "Payment",

                    "link_auto_reminders" => true,

                    "link_expiry_time" => now()->addHours(24)->format('Y-m-d\TH:i:sP'),

                    "link_meta" => [
                        "notify_url" => "https://login.connectpe.in/api/payin/callbacks/cashfree",
                        "return_url" => "https://login.connectpe.in/",
                        "upi_intent" => false,
                    ],

                    "link_notify" => [
                        "send_email" => true,
                        "send_sms" => true,
                    ],

                    "link_partial_payments" => false,

                    "link_notes" => [
                        "remarks" => "Payment Link",
                    ],

                    "enable_invoice" => true,
                ];
                // dd(
                //     'Cashfree API Version: ' . $this->cashfreeapiversion,
                //     'Cashfree App ID: ' . $this->cashfreeappid,
                //     'Cashfree Secret Key: ' . $this->cashfreesecretkey,
                //     $payload
                // );
                $response = Http::withHeaders([
                    'x-api-version' => $this->cashfreeapiversion,
                    'x-client-id' => $this->cashfreeappid,
                    'x-client-secret' => $this->cashfreesecretkey,
                    'Content-Type' => 'application/json',
                ])
                    ->timeout(40)
                    ->connectTimeout(30)
                    ->retry(
                        3,
                        2000,
                        function ($exception, $request) {
                            return $exception instanceof ConnectionException;
                        }
                    )
                    ->post($url, $payload);

                $result = $response->json();

                Log::info('Cashfree Payin Response', [
                    'request' => $payload,
                    'response' => $result,
                ]);

                return response()->json([
                    'data' => $result,
                    'status' => $response->successful(),
                    'message' => $response->successful() ? 'Payment initiated successfully' : 'API Error',
                ]);

                break;

            case 'easebuzz':
                try {

                    $validator->addRules([
                        'amount' => 'required|numeric|min:100',
                    ]);

                    $this->validateError($validator);

                    // GENERATE ACCESS KEY INTERNALLY
                    $feeData = TransactionHelper::payinFeeTaxDeduction($userId, $request->amount, $serviceId);

                    $accessKeyResponse = SeamlessPayinHelper::generateEasebuzzAccessKey([
                        'amount'         => $request->amount,
                        'firstname'      => $request->name,
                        'phone'          => $request->mobile_number,
                        'email'          => $request->email,
                        'transaction_id' => $request->transaction_id ?? null,
                    ]);

                    Log::info('accessKeyResponse', [
                        'accessKeyResponse' => $accessKeyResponse
                    ]);

                    if (!($accessKeyResponse['status'] ?? false) || empty($accessKeyResponse['access_key'] ?? null)) {
                        throw new Exception("Unable to generate Payment Gateway access key", 400);
                    }

                    $accessKey = $accessKeyResponse['access_key'] ?? null;

                    $data = [
                        'access_key'   => $accessKey,
                        'payment_mode' => 'UPI',
                        'upi_qr'       => 'true',
                        'request_mode' => 'SUVA',
                    ];

                    $url = 'https://pay.easebuzz.in/initiate_seamless_payment/';

                    $response = Http::asForm()
                        ->acceptJson()
                        ->post($url, $data);

                    $result = $response->json();

                    Log::info('Easebuzz Payin Response', [
                        'response' => $result,
                    ]);

                    if ($response->successful() && ($result['status'] ?? false) === true) {

                        $connectpeOrderId = CommonHelper::generateConnectPeTransactionId();
                        $intentUrl = $result['qr_link'] ?? null;

                        if ($intentUrl) {
                            $intentUrl = str_replace(
                                'refUrl=https://pay.easebuzz.in',
                                'refUrl=https://connectpe.in',
                                $intentUrl
                            );
                        }


                        $ourQrUrl = route('payin.qr', [
                            'clientRefId' => $request->transaction_id,
                        ]);


                        DB::table('seamless_upi_collections')->insert([
                            'cust_name' => $request->name,
                            'cust_mobile' => $request->mobile_number,
                            'cust_email' => $request->email,
                            'cust_txn_id' =>  $request->transaction_id,
                            'connectpe_order_id' => $connectpeOrderId,
                            'amount' => $request->amount,
                            'fee' => $feeData['fee'],
                            'tax' => $feeData['tax'],
                            'net_amount' => $feeData['netAmount'],
                            'user_id' => $userId,
                            'txn_order_id' => 'null',
                            'upi_intent' => $intentUrl,
                            'response' => json_encode($result),
                            'status' => 'pending',
                            'route'  => $providerSlug,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        return response()->json([
                            'status'  => true,
                            'message' => 'Payment initiated successfully',
                            'data' => [
                                'status' => 'pending',
                                'amount' => $request->amount,
                                'intent_url' => $intentUrl,
                                'qr_url' => $ourQrUrl,
                                'orderid' => $connectpeOrderId,
                                'txnid' => null,
                                'client_txn_id' => $request->transaction_id,
                                'created_at' => now()->format('d-m-Y h:i:s A'),
                            ]
                        ]);
                    }

                    throw new Exception("Unable to generate UPI deeplink", 404);
                } catch (\Exception $e) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Error : ' . $e->getMessage(),
                    ], 500);
                }
                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payin type.',
                ], 400);
        }
    }


    private function validateError($validator)
    {
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 422);
        }
    }

    public function qrCodeForPayin(string $clientRefId)
    {
        $transaction = SeamlessUpiCollection::where('cust_txn_id',  $clientRefId)->firstOrFail();

        if (empty($transaction->upi_intent)) {
            abort(404, 'QR code is not available.');
        }

        if ($transaction->created_at->addMinutes(8)->isPast()) {
            abort(404, 'QR code is expired.');
        }

        return view('payin.qr', compact('transaction'));
    }
}
