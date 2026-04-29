<?php

namespace App\Helpers;

use App\Models\UserConfig;
use App\Models\Transaction;
use App\Models\WebHookUrl;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TransactionHelper
{

    public static function sendPayinCallback($userId, $orderId, $payload, $serviceSlug)
    {
        $getWebhookUrl = WebHookUrl::where('user_id', $userId)
            ->where('service_slug', $serviceSlug)
            ->first();

        if (! $getWebhookUrl) {
            Log::warning("Webhook URL not found for userId {$userId}");

            return [
                'status' => false,
                'message' => 'Webhook URL not found'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->retry(2, 200)
                ->post($getWebhookUrl->url, $payload);

            if ($response->successful()) {

                Log::info('Callback sent successfully', [
                    'order_id' => $orderId,
                    'response' => $response->body(),
                ]);

                return [
                    'status' => true,
                    'message' => 'Callback sent successfully'
                ];
            }

            Log::error('Callback failed', [
                'order_id' => $orderId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            return [
                'status' => false,
                'message' => 'Callback failed'
            ];
        } catch (\Exception $e) {

            Log::error('Callback exception', [
                'order_id' => $orderId,
                'error'    => $e->getMessage()
            ]);

            return [
                'status' => false,
                'message' => 'Exception occurred'
            ];
        }
    }

    public static function sendPayoutCallback($userId, $orderRefId, $status, $serviceId)
    {
        //send callback
        $getWebhooks = WebHookUrl::where('user_id', $userId)->where('service_id', $serviceId)->first();
        if ($getWebhooks) {
            $order = Order::where('order_ref_id', $orderRefId)->first();
            $url = $getWebhooks['webhook_url'];
            $secret = $getWebhooks['secret'];
            if (isset($getWebhooks['header_key']) && isset($getWebhooks['header_value'])) {
                $headers = [$getWebhooks['header_key'] => $getWebhooks['header_value']];
                if ($status == 'processed') {
                    WebhookHelper::PayoutSuccess($order, $url, $secret, $headers);
                } else if ($status == 'failed') {
                    WebhookHelper::PayoutFailed($order, $url, $secret, $headers);
                } else if ($status == 'reversed') {
                    WebhookHelper::PayoutReverse($order, $url, $secret, $headers);
                }
            } else {
                if ($status == 'processed') {
                    WebhookHelper::PayoutSuccess($order, $url, $secret);
                } else if ($status == 'failed') {
                    WebhookHelper::PayoutFailed($order, $url, $secret);
                } else if ($status == 'reversed') {
                    WebhookHelper::PayoutReverse($order, $url, $secret);
                }
            }
        }
    }

    public static function sendCallback($userId, $reqId, $status)
    {
        $getWebhooks = WebHookUrl::where('user_id', $userId)->first();
        if ($getWebhooks) {
            $orderData = Transaction::where('request_id', $reqId)->first();
            $url = $getWebhooks['url'];


            WebhookHelper::RechargeTransaction($orderData, $url, '', '');
        } else {
            \Log::warning('Webhook Url not found of this userId ' . $userId);
        }
    }

    public function sendPaymentCallback($userId, $txnId)
    {
        try {
            $getWebhooks = WebHookUrl::where('user_id', $userId)->first();
            if ($getWebhooks) {
                $orderData = Transaction::where('request_id', $reqId)->first();
                $url = $getWebhooks['url'];


                WebhookHelper::RechargeTransaction($orderData, $url, '', '');
            } else {
                \Log::warning('Webhook Url not found of this userId ' . $userId);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public static function payinFeeTaxDeduction($userId, $totalAmount, $serviceId)
    {
        $schemeId = DB::table("user_configs")
            ->select("scheme_id")
            ->where("user_id", $userId)
            ->first();
        // dd($totalAmount);
        if ($schemeId) {
            $scheme = DB::table("scheme_rules")
                ->where("service_id", $serviceId)
                ->where("scheme_id", $schemeId->scheme_id)
                ->where("is_active", "1")
                ->where("start_value", "<=", $totalAmount)
                ->where("end_value", ">=", $totalAmount)
                ->orderBy("id", "desc")
                ->first();

            // dd($scheme);
        } else {
            return response()->json([
                "message" => "scheme is not defined yet for user id" . $userId,
            ]);
        }

        $feePercent = $scheme->fee;
        $taxPercent = 18;

        if (!$scheme->fee) {
            return response()->json([
                "message" => "fee not deducted",
            ]);
        }

        $fee = 0;
        $tax = 0;
        $netAmount = 0;

        // dd($scheme->type);
        if ($scheme->type == "Percentage") {
            $fee = ($totalAmount * $feePercent) / 100;
            $tax = ($fee * $taxPercent) / 100;
            $netAmount = $totalAmount - ($fee + $tax);
        } elseif ($scheme->type == "Fixed") {
            $fee = $feePercent;
            $taxPercentcal = $totalAmount - $fee;
            $tax = ($fee * $taxPercent) / 100;
            $netAmount = $totalAmount - ($fee + $tax);
        } else {

            return response()->json([
                "status" => false,
                "message" => "scheme type is not defined",
            ]);
        }


        $data = [];

        $data['fee'] = $fee;
        $data['tax'] = $tax;
        $data['netAmount'] = $netAmount;

        return $data;
    }

    public static function getFeesAndTaxes($amount, $serviceId, $userId = null)
    {
        if (!$userId) {
            return [
                "status" => false,
                "message" => "User ID is required"
            ];
        }

        $schemeData = DB::table("user_configs")
            ->select("scheme_id")
            ->where("user_id", $userId)
            ->first();

        if (!$schemeData) {
            return [
                "status" => false,
                "message" => "Scheme is not defined for user id: " . $userId
            ];
        }

        $scheme = DB::table("scheme_rules")
            ->where("service_id", $serviceId)
            ->where("scheme_id", $schemeData->scheme_id)
            ->where("is_active", '1')
            ->where("start_value", "<=", $amount)
            ->where("end_value", ">=", $amount)
            ->orderByDesc("id")
            ->first();

        // dd($scheme);
        if (!$scheme) {
            return [
                "status" => false,
                "message" => "No scheme rule found for this amount range"
            ];
        }

        //success response start
        $response = [
            "status" => true,
            "amount" => (float) $amount,
            "fee" => 0,
            "tax" => 0,
            "margin" => '',
            "total_amount" => 0,
            "scheme" => $scheme->scheme_id ?? ''
        ];

        // Fee calculation
        if ($scheme->type == 'Percentage') {

            $calculatedFee = round(($amount * $scheme->fee) / 100, 4, PHP_ROUND_HALF_EVEN);

            if (!empty($scheme->min_fee) && $calculatedFee < $scheme->min_fee) {
                $response['fee'] = (float) $scheme->min_fee;
                $response['margin'] = 'fixed@' . $response['fee'];
            } elseif (!empty($scheme->max_fee) && $calculatedFee > $scheme->max_fee) {
                $response['fee'] = (float) $scheme->max_fee;
                $response['margin'] = 'fixed@' . $response['fee'];
            } else {
                $response['fee'] = (float) $calculatedFee;
                $response['margin'] = 'percentage@' . $scheme->fee;
            }
        } else {
            $response['fee'] = (float) $scheme->fee;
            $response['margin'] = 'fixed@' . $scheme->fee;
        }

        // Tax
        $response['tax'] = round(($response['fee'] * 18) / 100, 4, PHP_ROUND_HALF_EVEN);

        // Total
        $response['total_amount'] = round(
            $response['amount'] + $response['fee'] + $response['tax'],
            4,
            PHP_ROUND_HALF_EVEN
        );

        return $response;
    }

    public static function createTransactionAndOrder($orderRefId, $userId, $serviceId, $modeId, $providerId, $orderArray)
    {
        DB::beginTransaction();

        try {
            $charges = $orderArray['charges'];

            $totalAmount = $charges['amount'] + $charges['fee'] + $charges['tax'];

            $amountPositiveInt = self::intPositive($totalAmount);
            if (! $amountPositiveInt['status']) {
                return [
                    'status'  => false,
                    'message' => 'Invalid transaction amount'
                ];
            }



            // Remark handling
            $remarksData = $orderArray['remark'] ?? 'Fund Transfer';

            // Narration (fixed concatenation)
            $narration = 'amount : ' . $charges['amount'] .
                ' , charge : ' . $charges['fee'] .
                ' , tax : ' . $charges['tax'] .
                (isset($charges['margin']) ? ' , margin : ' . $charges['margin'] : '');

            $connectpeId = CommonHelper::generateConnectPeTransactionId();

            $orderData = [
                'contact_id'    => $orderArray['contactId'],
                'connectpe_id'  => $connectpeId,
                'mode_id'       => $modeId,
                'service_id'    => $serviceId,
                'provider_id'   => $providerId,
                'client_ref_id' => $orderArray['clientRefId'],
                'narration'     => $narration,
                'user_id'       => $userId,
                'order_ref_id'  => $orderRefId,
                'currency'      => 'INR',
                'amount'        => $charges['amount'],
                'fee'           => $charges['fee'],
                'tax'           => $charges['tax'],
                'total_amount'  => $totalAmount,
                'mode'          => CommonHelper::caseConversion($orderArray['mode'], 'u'),
                'purpose'       => CommonHelper::caseConversion($orderArray['purpose'], 'u'),
                'remark'        => $remarksData,
                'ip'            => $orderArray['agent']['ip'] ?? null,
                'user_agent'    => $orderArray['agent']['userAgent'] ?? null,
                'status'        => 'queued',
                'updated_by'    => $userId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            $orderId = DB::table('orders')->insertGetId($orderData);

            DB::commit();

            return [
                'status'  => true,
                'message' => 'Order created successfully.',
                'order_id' => $orderId
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ];
        }
    }

    public static function createRechargeTransactionOrders($userId, $serviceId, $providerId, $payload)
    {
        try {
            DB::beginTransaction();

            $remarksData = 'Fund Transfer';

            $connectpeId = CommonHelper::generateConnectPeTransactionId();

            $orderData = [
                'user_id'       => $userId,
                'provider_id'   => $providerId,
                'service_id'    => $serviceId,
                'connectpe_id'  => $connectpeId,
                'request_id'       => $modeId,
                'payment_ref_id ' => $ghdjkfdk,
                'connection_no' => $hjf,
                'operator_id' => $hgdg,
                'circle_id' => $dghdj,
                'plan_type' => $gfgnfj,
                'customer_mobile' => $jhgjdh,
                'agent_id' => $ffhb,
                'remitter_name' => $xhdfhgd,
                'payment_mode' => $vmcvf,
                'payment_account_info' => $fdhgdjfh,
                'recharge_type' => $fndjnbd,
                'amount'        => $charges['amount'],
                'remark'        => $remarksData,
                'ip'            => $orderArray['agent']['ip'] ?? null,
                'user_agent'    => $orderArray['agent']['userAgent'] ?? null,
                'status'        => 'queued',
                'updated_by'    => $userId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            $orderId = DB::table('recharge_orders')->insertGetId($orderData);

            DB::commit();

            return [
                'status'  => true,
                'message' => 'Recharge Order created successfully.',
                'request_id' => $orderId
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ];
        }
    }

    public static function intPositive($num)
    {
        if ($num < 0) {
            $response['status']  = false;
            $response['message'] = 'Negative integer value';
        } else {
            $response['status']  = true;
            $response['message'] = 'Positive integer value';
        }
        return $response;
    }

    public static function moveOrderToProcessingByOrderId($userId, $orderRefId, $connectpeId, $providerId)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {
            $txn = CommonHelper::getRandomString('txn', false);
            DB::select("CALL debitPayoutBalanceOrder($userId, '" . $orderRefId . "',  '" . $connectpeId . "','" . $providerId . "', '" . $txn . "', @json)");
            $results = DB::select('select @json as json');
            $response = json_decode($results[0]->json, true);
            if ($response['status'] == '1') {
                $resp['status'] = true;
                $resp['message'] = 'Order processing successfully.';
            } else {
                $resp['status'] = false;
                $resp['message'] = $response['message'];
            }
        } catch (\Exception $e) {
            $resp['status'] = false;
            $resp['message'] = 'Some errors : ' . $e->getMessage();
        }
        return $resp;
    }
}
