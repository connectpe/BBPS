<?php

namespace App\Helpers;

use App\Models\UserConfig;
use App\Models\Transaction;
use App\Models\WebHookUrl;
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
        $response = [
            'amount' => (float) $amount,
            'fee' => 0,
            'tax' => 0,
            'margin' => '',
            'total_amount' => 0,
            'scheme' => ''
        ];

        if (!$userId) {
            return [
                "status" => false,
                "message" => "User ID is required"
            ];
        }

        // Get scheme id
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

        // Get scheme rule
        $scheme = DB::table("scheme_rules")
            ->where("service_id", $serviceId)
            ->where("scheme_id", $schemeData->scheme_id)
            ->where("is_active", 1)
            ->where("start_value", "<=", $amount)
            ->where("end_value", ">=", $amount)
            ->orderByDesc("id")
            ->first();

        if (!$scheme) {
            return [
                "status" => false,
                "message" => "No scheme rule found for this amount range"
            ];
        }

        $response['scheme'] = $scheme->scheme_id ?? '';

        // Calculate fee
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
        } elseif ($scheme->type == 'Fixed') {

            $response['fee'] = (float) $scheme->fee;
            $response['margin'] = 'fixed@' . $scheme->fee;
        }

        // Tax calculation (GST 18%)
        $taxPercent = 18;
        $response['tax'] = round(($response['fee'] * $taxPercent) / 100, 4, PHP_ROUND_HALF_EVEN);

        // Total amount
        $response['total_amount'] = round(
            $response['amount'] + $response['fee'] + $response['tax'],
            4,
            PHP_ROUND_HALF_EVEN
        );

        return $response;
    }

    public static function createTransactionAndOrder($orderRefId, $userId, $serviceId, $modeId, $orderArray)
    {
        $response['status']  = false;
        $response['message'] = 'Order Not created';
        DB::beginTransaction();
        try {
            $totalAmount       = $orderArray['charges']['amount'] + $orderArray['charges']['fee'] + $orderArray['charges']['tax'];
            $amountPositiveInt = self::intPositive($totalAmount);
            if ($amountPositiveInt['status']) {
                if (empty($orderArray['remark'])) {
                    $businessInfo = DB::table('business_infos')->where('user_id', $userId)->first();
                    if (! empty($businessInfo) && ! empty($businessInfo->business_name)) {
                        $remarksData = "Fund Transfer";
                    } else {
                        $remarksData = "Fund Transfer";
                    }
                } else {
                    $remarksData = $orderArray['remark'];
                }
                // Transaction Create
                $orderData = [
                    'contact_id'    => $orderArray['contactId'],
                    'product_id'    => $modeId,
                    'service_id'    => $serviceId,
                    'client_ref_id' => $orderArray['clientRefId'],
                    'narration'     => isset($orderArray['narration']) ? $orderArray['narration'] : "",
                    'user_id'       => $userId,
                    'order_ref_id'  => $orderRefId,
                    'currency'      => 'INR',
                    'amount'        => $orderArray['charges']['amount'],
                    'fee'           => $orderArray['charges']['fee'],
                    'tax'           => $orderArray['charges']['tax'],
                    'mode'          => CommonHelper::case($orderArray['mode'], 'u'),
                    'purpose'       => CommonHelper::case($orderArray['purpose'], 'u'),
                    'remark'        => $remarksData,
                    'mode'          => CommonHelper::case($orderArray['mode'], 'u'),
                    'txt_3'         => $orderArray['charges']['margin'],
                    'ip'            => $orderArray['agent']['ip'],
                    'area'          => '11',
                    'user_agent'    => $orderArray['agent']['userAgent'],
                    'status'        => 'queued',
                ];
                if (isset($orderArray['udf1'])) {
                    $orderData = array_merge($orderData, ['udf1' => $orderArray['udf1']]);
                }
                if (isset($orderArray['udf2'])) {
                    $orderData = array_merge($orderData, ['udf2' => $orderArray['udf2']]);
                }

                $createTransaction = DB::table('orders')->insert($orderData);
                if ($createTransaction) {
                    DB::commit();
                    $response['status']  = true;
                    $response['message'] = 'Order created successfully.';
                } else {
                    $response['status']  = false;
                    $response['message'] = 'Order not created.';
                }
            } else {
                $response['status']  = false;
                $response['message'] = 'Invalid transaction amount';
            }
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            $response['status']  = false;
            $response['message'] = 'something went wrong : ' . $e->getMessage();
        }
        return $response;
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

    public static function moveOrderToProcessingByOrderId($userId, $orderRefId, $integrationId = null)
    {
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
        try {
            $txn = CommonHelper::getRandomString('txn', false);
            DB::select("CALL debitPayoutBalanceOrder($userId, '" . $orderRefId . "', '" . $integrationId . "', '" . $txn . "', @json)");
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
