<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\HashHelper;
use App\Helpers\CommonHelper;

class PayoutOrderController extends Controller
{
    public function createOrder(Request $request)
    {

        Log::info("Calling Function store");

        // Get credentials
        $client_id = $request->getUser();
        $client_secret = hash('sha512', $request->getPassword());

        //making hash
        $hash = HashHelper::generate(HashHelper::CREATE_ORDER, $client_id, $client_secret, $request->all());

        //user signature
        $signature = isset($header['signature'][0]) ? $header['signature'][0] : '';

        $userId = $request["auth_data"]['user_id'];
        $serviceId = $request["auth_data"]['service_id'];

        $status = CommonHelper::isGlobalServiceActive($serviceId);
        // dd($status);

        if (!$status) {
            return response()->json([
                'status' => false,
                'message' => 'Downtime started now'
            ]);
        }
        $validation = new Validations($request);
        $validator = $validation->addOrder();

        $validator->after(function ($validator) use ($request, $userId, $hash, $signature) {

            //match signature
            if (strpos($request->amount, ".") !== false) {
                if (strlen(strrchr($request->amount, '.')) - 1 > 2) {
                    $validator->errors()->add('amount', 'Only 2 decimal except in amount');
                }
            }



            $User = User::where('id', $userId)->where('is_active', '1')->first();
            if (empty($User)) {
                $validator->errors()->add('userId', 'User Account disabled');
            } else {
                $isAvailable = DB::table('user_services')
                    ->where(['user_id' => $userId, 'service_id' => PAYOUT_SERVICE_ID])
                    ->select('is_active', 'transaction_amount')->first();
                if (isset($isAvailable) && $isAvailable->is_active == '1') {

                    $totalAmount =  $request->amount;
                    if ($totalAmount >= $isAvailable->transaction_amount) {
                        $validator->errors()->add('amount', 'Insufficient funds.');
                    }

                    // if ($totalAmount < 100) {
                    //   $validator->errors()->add('amount', 'Total amount should be at least 100.');
                    // }

                } else {
                    $validator->errors()->add('userId', 'Your payout service is disabled.');
                }
            }

            $Contact = DB::table('contacts')->select('user_id', 'account_number', 'account_type')->where('contact_id', $request->contactId)->where('user_id', $userId)->first();
            if (empty($Contact)) {
                $validator->errors()->add('contactId', 'contactId is not valid');
            } else {

                $mode = CommonHelper::case($request->mode, 'l');
                if ($Contact->account_type == 'bank_account') {
                    if (!in_array($mode, ['neft', 'imps', 'rtgs'])) {
                        $validator->errors()->add('mode', 'Please send valid mode example. imps, neft, rtgs');
                    }
                } elseif ($Contact->account_type == 'vpa') {
                    if ($mode != 'upi') {
                        $validator->errors()->add('mode', 'Please send valid mode example. upi');
                    }
                } elseif ($Contact->account_type == 'card') {
                    if ($mode != 'card') {
                        $validator->errors()->add('mode', 'Please send valid mode example. card');
                    }
                }
            }
            $Order = Order::where('client_ref_id', $request->clientRefId)->count();
            if ($Order) {
                $validator->errors()->add('clientRefId', 'Client Ref Id all ready exists.');
            }
        });
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ApiResponseHelper::missing($errors);
        } else {

            $UConfig = UserConfig::where(['user_id' => $userId, 'api_integration_id' => 'int_1654155017'])
                ->count();
            if ($UConfig == 1) {
                $GlobalConfig = GlobalConfig::select('attribute_1', 'attribute_2', 'attribute_3', 'attribute_4')
                    ->where(['slug' => 'partner_account_balance', 'attribute_4' => '1'])
                    ->first();
                if (isset($GlobalConfig) && !empty($GlobalConfig)) {
                    if ($GlobalConfig->attribute_3 < $request->amount) {
                        return ResponseHelper::failed('E40321:Something went wrong, please try after some time.');
                    }
                }
            }

            $data = $request->all();
            $getProductId = CommonHelper::getProductId($data['mode'], 'payout');
            $productId = '';
            $serviceId = '';
            if ($getProductId) {
                $productId = $getProductId->product_id;
                $serviceId = $getProductId->service_id;
            }

            $getProductConfig = TransactionHelper::getProductConfig($data['mode'], $serviceId);
            if ($getProductConfig['status']) {
                if ($getProductConfig['data']['min_order_value'] <= $data['amount'] && $getProductConfig['data']['max_order_value'] >= $data['amount']) {
                    // Get Total Amount Fee and Tax Amount
                    $orderLastWitoutProcess = DB::table('orders')->select('created_at')
                        ->where('user_id', $userId)
                        ->orderBy('id', 'desc')->first();
                    $orderRefId = CommonHelper::getRandomString2('REF', false);
                    $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($productId, $data['amount'], $userId);
                    $header = $request->header();
                    $data['agent']['ip'] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                    $data['agent']['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "";
                    $data['charges'] = $getFeesAndTaxes;

                    $orderCreate = TransactionHelper::createTransactionAndOrder($orderRefId, $userId, $serviceId, $productId, $data);
                    if ($orderCreate['status']) {

                        dispatch(new \App\Jobs\PayoutBalanceDebitAndStatusUpdateJob($orderRefId, $userId, 'balance_debit', '', '', '', '', ''))->onQueue('payout_debit_queue');


                        $orderInfo = ['clientRefId' => $data['clientRefId'], 'orderRefId' => $orderRefId, 'status' => 'queued'];
                        return ResponseHelper::success('Order accepted successfully', $orderInfo, '200');
                    } else {
                        return ResponseHelper::failed($orderCreate['message'], []);
                    }
                } else {
                    $checkAndLock['message'] = $data['amount'] . ' Provided amount is not in range for ' . $data['mode'] . ' Transaction';
                    return ResponseHelper::failed($checkAndLock['message'], []);
                }
            } else {
                $checkAndLock['message'] = $getProductConfig['message'];
                return ResponseHelper::failed($checkAndLock['message'], []);
            }
        }
    }
}
