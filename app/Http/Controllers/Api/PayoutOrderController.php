<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\HashHelper;
use App\Helpers\CommonHelper;
use App\Helpers\TransactionHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Validation\OrderValidation;
use App\Models\User;

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

        if (!$status['status']) {
            return response()->json([
                'status' => false,
                'message' => $status['message']
            ]);
        }

        $validation = new OrderValidation($request);
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
                $totalAmount = $User->transaction_amount;
                $amount = $request->amount;
                if ($amount > $totalAmount) {
                    $validator->errors()->add('amount', 'Insufficient funds.');
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

            $data = $request->all();
            $getModeId = CommonHelper::getModeId($data['mode'], $serviceId);

            // $getProductConfig = TransactionHelper::getProductConfig($data['mode'], $serviceId);
            if ($getModeId['status']) {
                if ($getModeId['data']->min_order_value <= $data['amount'] && $getModeId['data']->max_order_value >= $data['amount']) {

                    $modeId = $getModeId['data']->mode_id;
                    $orderRefId = CommonHelper::getRandomString2('REF', false);
                    $getFeesAndTaxes = TransactionHelper::getFeesAndTaxes($data['amount'],  $serviceId ,$userId);
                    $header = $request->header();
                    $data['agent']['ip'] = isset($header["cf-connecting-ip"][0]) ? $header["cf-connecting-ip"][0] : $request->ip();
                    $data['agent']['userAgent'] = isset($header["user-agent"][0]) ? $header["user-agent"][0] : "";
                    $data['charges'] = $getFeesAndTaxes;

                    $orderCreate = TransactionHelper::createTransactionAndOrder($orderRefId, $userId, $serviceId, $modeId, $data);
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
