<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayinOrdersController extends Controller
{
    public function createOrders(Request $request)
    {
        $userId = CommonHelper::getUserIdUsingKeyAndSecret($request->header());
        if (!$userId) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ]);
        }
        $data = DB::table('users')->where('id', $userId)->first();
        $findtype = DB::table('global_config')->select('attribute_1')->where('slug', 'default_payin_route')->first();
        // $type = $data->payin_switch ?$data->payin_switch:'rabbitpe';
        $type = $findtype->attribute_1;

        $status = CommonHelper::isServiceEnabled($userId, 'srv_162607709190', 'isserviceEnabled');
        // if(!$status){
        //     return response()->json([
        //         'status'=> false,
        //         'message'=> 'Downtime started now'
        //     ]);
        // }

        // return response()->json([
        //     'status'=> false,
        //     'message'=> 'Downtime started now'
        // ]);
        // if($userId == '554'){
        //     $type = 'laraware';
        // } 

        // return response()->json([
        //     'status' => false,
        //     'message' => 'Service is under maintenance',
        // ]);

        $isActive = CommonHelper::isuserActiveServiceAccount($userId);

        if (!$isActive) {
            return response()->json([
                'status' => false,
                'message' => 'Your are inactive user, Please contact to the administrator',

            ]);
        }

        $isActiveServices = CommonHelper::isServiceEnabled($userId, 'srv_162607709190', 'isserviceEnabled');
        //  dd($isActiveServices);
        if (!$isActiveServices) {
            return response()->json([
                'status' => false,
                'message' => 'Service is down Please Contact to the admin',

            ]);
        }
        switch ($type) {
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

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payin type.',
                ], 400);
        }
    }
}
