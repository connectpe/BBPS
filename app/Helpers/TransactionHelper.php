<?php

namespace App\Helpers;

class TransactionHelper{

    public static function moveOrderToProcessingByOrderId($userId , $reqId){
        $resp['status'] = false;
        $resp['message'] = 'Initiate';
    }

    public static function sendCallback($userId,$reqId,$status){
        $getWebhooks = WebHookUrl::where('user_id', $userId)->first();
         if ($getWebhooks) {
             $orderData = Transaction::where('request_id', $reqId)->first();
             $url = $getWebhooks['url'];
             
            
            WebhookHelper::RechargeTransaction($orderData,$url,'','');
             
        }
    }

    public function IsUserServiceEnable($status){

    }
}