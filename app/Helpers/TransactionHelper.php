<?php

namespace App\Helpers;

use App\Models\UserConfig;
use App\Models\Transaction;
use App\Models\WebHookUrl;
use Illuminate\Support\Facades\DB;

class TransactionHelper
{

    // public static function moveOrderToProcessingByOrderId($userId , $reqId){
    //     $resp['status'] = false;
    //     $resp['message'] = 'Initiate';
    // }

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

    public static function getFeesAndTaxes($provider_id, $amount, $userId = null)
    {
        $response['amount'] = $amount;
        $response['fee'] = 0;
        $response['tax'] = 0;
        $response['margin'] = '';
        $response['total_amount'] = 0;
        $response['scheme'] = '';

        $globalConfig = UserConfig::where('user_id', $userId)->select('scheme_id')->where('is_active', '1')->first();
        $is_custom_fee_active = empty($globalConfig) ? '0' : $globalConfig->scheme_id;

        if ($userId !== null && $is_custom_fee_active === '1') {

            $fee = DB::table('user_config as uc')
                ->select('fee', 'type', 'is_active')
                ->leftJoin('scheme_rules as sr', 'sr.scheme_id', 'uc.scheme_id')
                ->leftJoin('providers as p', 'p.service_id', 'sr.service_id')
                ->leftJoin('schemes', 'uc.scheme_id', 'schemes.id')
                ->where('p.id', $provider_id)
                ->where('sr.start_value', '<=', $amount)
                ->where('sr.end_value', '>=', $amount)
                ->where('sr.is_active', '1')
                ->where('schemes.is_active', '1')
                ->whereNotNull('uc.scheme_id')
                ->where('uc.user_id', $userId)
                ->first();

            if (empty($fee)) {
                $fee = DB::table('user_config as uc')
                    ->select('sr.*')
                    ->leftJoin('scheme_rules as sr', 'sr.scheme_id', 'uc.scheme_id')
                    ->leftJoin('providers as p', 'p.service_id', 'sr.service_id')
                    ->leftJoin('schemes', 'uc.scheme_id', 'schemes.id')
                    ->where('p.id', $provider_id)
                    ->whereNull('sr.start_value')
                    ->whereNull('sr.end_value')
                    ->where('sr.is_active', '1')
                    ->where('schemes.is_active', '1')
                    ->whereNotNull('uc.scheme_id')
                    ->where('uc.user_id', $userId)
                    ->first();
            }

            $response['scheme'] = 'custom';
        }


        //fetch info from global
        // if (empty($fee)) {
        //     $fee = DB::table('global_product_fees as gpf')
        //         ->select('gpf.*', 'gp.tax_value')
        //         ->leftJoin('global_products as gp', 'gp.product_id', 'gpf.product_id')
        //         ->where('gpf.product_id', $product_id)
        //         ->where('gpf.start_value', '<=', $amount)
        //         ->where('gpf.end_value', '>=', $amount)
        //         ->where('gpf.is_active', '1')
        //         ->first();

        //     $response['scheme'] = 'global';
        // }

        // if (empty($fee)) {

        //     $fee = DB::table('global_product_fees as gpf')
        //         ->select('gpf.*', 'gp.tax_value')
        //         ->leftJoin('global_products as gp', 'gp.product_id', 'gpf.product_id')
        //         ->where('gpf.product_id', $product_id)
        //         ->whereNull('gpf.start_value')
        //         ->whereNull('gpf.end_value')
        //         ->where('gpf.is_active', '1')
        //         ->first();

        //     $response['scheme'] = 'global';
        // }


        if (!empty($fee)) {
            if ($fee->type == 'percent') {
                // $response['fee'] = round(((float) $amount) * $fee->fee / 100, 4, PHP_ROUND_HALF_EVEN);

                $calculatedFee = round(((float) $amount) * (float) $fee->fee / 100, 4, PHP_ROUND_HALF_EVEN);

                if ((float) $calculatedFee < (float) $fee->min_fee && !empty($fee->min_fee)) {
                    $response['fee'] = (float) $fee->min_fee;
                    $response['margin'] =  'fixed' . '@' . $response['fee'];
                } elseif ((float) $calculatedFee > (float) $fee->max_fee && !empty($fee->max_fee)) {
                    $response['fee'] = (float) $fee->max_fee;
                    $response['margin'] =  'fixed' . '@' . $response['fee'];
                } else {
                    $response['fee'] = (float) $calculatedFee;
                    $response['margin'] = $fee->type . '@' . $fee->fee;
                }
            } else if ($fee->type == 'fixed') {
                $response['fee'] = $fee->fee;
                $response['margin'] = $fee->type . '@' . $fee->fee;
            }

            $fee->tax_value = !empty($fee->tax_value) ? $fee->tax_value : 18;

            $response['amount'] = $amount;
            $response['tax'] = round($response['fee'] * $fee->tax_value / 100, 4, PHP_ROUND_HALF_EVEN);
            // $response['margin'] = $fee->type . '@' . $fee->fee;
        }



        $response['total_amount'] = $response['amount'] + $response['fee'] + $response['tax'];

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
