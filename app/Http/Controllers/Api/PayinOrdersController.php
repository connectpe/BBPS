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

class PayinOrdersController extends Controller
{

    private $cashfreePayinUrl;
    private $cashfreeappid;
    private $cashfreesecretkey;
    private $cashfreeapiversion;

    public function __construct()
    {
        $this->cashfreePayinUrl = config('payin.cashfree_url');
        $this->cashfreeappid = config('payin.cashfree_app_id');
        $this->cashfreesecretkey = config('payin.cashfree_secret_key');
        $this->cashfreeapiversion = config('payin.cashfree_api_version');
    }



    public function createOrders(Request $request)
    {
        $userIdAndServiceId = CommonHelper::getUserIdAndServiceIdUsingKeyAndSecret($request->header());
        // dd($userIdAndServiceId);

        if (!$userIdAndServiceId['status']) {
            return response()->json([
                'status' => false,
                'message' => $userIdAndServiceId['message']
            ]);
        }

        $userId = $userIdAndServiceId['user_id'] ?? null;
        $serviceId = $userIdAndServiceId['service_id'] ?? null;
        
        $activeUser = User::where('id', $userId)->where('status', '1')->first();
       
        if (!$activeUser) {
            return response()->json([
                'message' => 'Your are inactive user, Please contact to the administrator'
            ]);
        }

        $isKyc = BusinessInfo::where('user_id', $userId)->where('is_kyc', '1')->first();

        if (!$isKyc) {
            return response()->json([
                'message' => 'KYC not completed'
            ]);
        }

        $isGlobalServiceActive = CommonHelper::isGlobalServiceActive($serviceId);

        if (!$isGlobalServiceActive['status']) {
            return response()->json([
                'status' => false,
                'message' => $isGlobalServiceActive['message']
            ], 400);
        }

        $isUserServiceActive = CommonHelper::isUserServiceActiveUsingUserIdAndServiceId($userId, $serviceId);

        if (!$isGlobalServiceActive['status']) {
            return response()->json([
                'status' => false,
                'message' => $isUserServiceActive['message']
            ], 400);
        }

        $getProviderSlug = CommonHelper::getProviderSlug($userId, $serviceId);

        if (!$getProviderSlug) {
            return response()->json([
                'message' => 'Provider not found for the user and service'
            ]);
        }

        $providerSlug = $getProviderSlug['provider_slug'] ?? null;

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

                    $url = $this->cgpeyPayinUrl;


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

                    $alldata = FeeTaxDedectionHelper::FeeTaxDeduction($userId, $request->amount);

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
                // dd($url);

                $payload = [
                    "customer_details" => [
                        "customer_name" => $request->name,
                        "customer_email" => $request->email,
                        "customer_phone" => $request->mobileNumber
                    ],
                    "link_amount" => $request->amount,
                    "link_currency" => "INR",
                    "link_id" => $request->cust_txn_id,
                    "link_purpose" => "Payment"
                ];

                // dd($this->cashfreeapiversion, $this->cashfreeappid, $this->cashfreesecretkey, $payload);

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
                dd($result);

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

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payin type.',
                ], 400);
        }
    }
}
