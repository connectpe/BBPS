<?php

namespace App\Helpers;

use App\Http\Controllers\Clients\Api\v1\DMTController;
use Illuminate\Http\Request;
use CommonHelper;
use App\Http\Webhooks\UPIWebhook;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WebhookHelper {

    

    
    /**
     * Recharge Transaction
     *
     * @param object $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function RechargeTransaction($data, $url = '',$secret = '', $headers = '')
    {

        if ($data->status == 'SUCCESS') {
            $arrayPayLoad['event'] = 'recharge.transfer.success';
            $arrayPayLoad['code'] = "0x0200";
            $arrayPayLoad['message'] = 'Transaction Successful';
        } else {
            $arrayPayLoad['event'] = 'recharge.transfer.failed';
            $arrayPayLoad['code'] = "0x0202";
            $arrayPayLoad['message'] = 'Transaction Failed';
        }
        $arrayPayLoad['data'] = [
            'clientRefNo' => @$data->payment_ref_id,
            
            'connectpe_id' =>  @$data->connectpe_id,
            'bankMessage' =>'',
            'status' => @$data->status,
        ];
        

      
        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    


    /**
     * Payout Transfer Success
     *
     * @param array $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function PayoutSuccess($data = [], $url = '', $secret = '', $headers = '')
    {

        $arrayPayLoad['event'] = 'payout.transfer.success';
        $arrayPayLoad['code'] = "0x0200";
        $arrayPayLoad['message'] = 'Transaction Successful';
        $arrayPayLoad['data'] = [
            'orderRefId' => @$data->order_ref_id,
            'clientRefId' => @$data->client_ref_id,
            'contactId' => @$data->contact_id,
            'firstName' => @$data->contact->first_name,
            'lastName' => @$data->contact->last_name,
            'email' => @$data->contact->email,
            'phone' => @$data->contact->phone,
            'amount' => @$data->amount,
            'status' => @$data->status,
            'utr' => @$data->bank_reference,
            //'udf1' => @$data->udf1,
            //'udf2' => @$data->udf2
        ];
        if ($data->contact->account_type == 'bank_account') {
            $arrayPayLoad['data']['accountNumber'] =  @$data->contact->account_number;
            $arrayPayLoad['data']['accountIFSC'] =  @$data->contact->account_ifsc;
        } elseif ($data->contact->account_type == 'vpa') {
            $arrayPayLoad['data']['vpa'] =  @$data->contact->vpa_address;
        } elseif ($data->contact->account_type == 'card') {
            $arrayPayLoad['data']['card'] =  @$data->contact->card_number;
        }


        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
                //->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    /**
     * Payout Transfer Failed
     *
     * @param array $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function PayoutFailed($data = [], $url = '', $secret = '', $headers = '')
    {
        $arrayPayLoad['event'] = 'payout.transfer.failed';
        $arrayPayLoad['code'] = "0x0202";
        $arrayPayLoad['message'] = 'Transaction Failed';
        $arrayPayLoad['data'] = [
            'orderRefId' => @$data->order_ref_id,
            'clientRefId' => @$data->client_ref_id,
            'contactId' => @$data->contact_id,
            'firstName' => @$data->contact->first_name,
            'lastName' => @$data->contact->last_name,
            'email' => @$data->contact->email,
            'phone' => @$data->contact->phone,
            'amount' => @$data->amount,
            'status' => @$data->status,
            'reason' => @$data->failed_message,
            //'udf1' => @$data->udf1,
            //'udf2' => @$data->udf2
        ];

        if ($data->contact->account_type == 'bank_account') {
            $arrayPayLoad['data']['accountNumber'] =  @$data->contact->account_number;
            $arrayPayLoad['data']['accountIFSC'] =  @$data->contact->account_ifsc;
        } elseif ($data->contact->account_type == 'vpa') {
            $arrayPayLoad['data']['vpa'] =  @$data->contact->vpa_address;
        } elseif ($data->contact->account_type == 'card') {
            $arrayPayLoad['data']['card'] =  @$data->contact->card_number;
        }
        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
               // ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    /**
     * Payout Transfer Reverse
     *
     * @param array $data
     * @param string $url
     * @param string $secret
     * @param string $headers
     * @return void
     */
    public static function PayoutReverse($data = [], $url = '', $secret = '', $headers = '')
    {
        $arrayPayLoad['event'] = 'payout.transfer.reverse';
        $arrayPayLoad['code'] = "0x0207";
        $arrayPayLoad['message'] = 'Transaction Reversed';
        $arrayPayLoad['data'] = [
            'orderRefId' => @$data->order_ref_id,
            'clientRefId' => @$data->client_ref_id,
            'contactId' => @$data->contact_id,
            'firstName' => @$data->contact->first_name,
            'lastName' => @$data->contact->last_name,
            'email' => @$data->contact->email,
            'phone' => @$data->contact->phone,
            'amount' => @$data->amount,
            'status' => @$data->status,
            'reason' => @$data->failed_message,
            //'udf1' => @$data->udf1,
            //'udf2' => @$data->udf2
        ];

        if ($data->contact->account_type == 'bank_account') {
            $arrayPayLoad['data']['accountNumber'] =  @$data->contact->account_number;
            $arrayPayLoad['data']['accountIFSC'] =  @$data->contact->account_ifsc;
        } elseif ($data->contact->account_type == 'vpa') {
            $arrayPayLoad['data']['vpa'] =  @$data->contact->vpa_address;
        } elseif ($data->contact->account_type == 'card') {
            $arrayPayLoad['data']['card'] =  @$data->contact->card_number;
        }

        if($headers) {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
                ->withHeaders($headers)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        } else {
            $data = \Spatie\WebhookServer\WebhookCall::create()
                ->url($url)
                ->payload($arrayPayLoad)
                ->useSecret($secret)
              //  ->timeoutInSeconds(5)
                ->maximumTries(3)
                ->dispatch();
        }
    }

    
}