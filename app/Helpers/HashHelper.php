<?php

namespace App\Helpers;


class HashHelper
{
    const CREATE_CONTACT = 'create_contact';
    const CREATE_ORDER = 'create_order';


    public static function generate($reqType, $clientKey, $salt, $params = null)
    {

        switch ($reqType) {

            case self::CREATE_CONTACT:
                $arr = [];
                if (isset($params['accountType']) && $params['accountType'] == 'bank_account') {
                    $arr = [
                        'firstName' => isset($params['firstName']) ? $params['firstName'] : '',
                        'lastName' => isset($params['lastName']) ? $params['lastName'] : '',
                        'email' => isset($params['email']) ? $params['email'] : '',
                        'mobile' => isset($params['mobile']) ? $params['mobile'] : '',
                        'type' => isset($params['type']) ? $params['type'] : '',
                        'accountType' => isset($params['accountType']) ? $params['accountType'] : '',
                        'accountNumber' => isset($params['accountNumber']) ? $params['accountNumber'] : '',
                        'bankName' => isset($params['bankName']) ? $params['bankName'] : '',
                        'ifsc' => isset($params['ifsc']) ? $params['ifsc'] : '',
                        'referenceId' => isset($params['referenceId']) ? $params['referenceId'] : '',
                    ];
                } elseif (isset($params['accountType']) && $params['accountType'] == 'vpa') {
                    $arr = [
                        'firstName' => isset($params['firstName']) ? $params['firstName'] : '',
                        'lastName' => isset($params['lastName']) ? $params['lastName'] : '',
                        'email' => isset($params['email']) ? $params['email'] : '',
                        'mobile' => isset($params['mobile']) ? $params['mobile'] : '',
                        'type' => isset($params['type']) ? $params['type'] : '',
                        'accountType' => isset($params['accountType']) ? $params['accountType'] : '',
                        'vpa' => isset($params['vpa']) ? $params['vpa'] : '',
                        'referenceId' => isset($params['referenceId']) ? $params['referenceId'] : '',
                    ];
                }
                $str = base64_encode(json_encode($arr));

                $str .= "/api/payout/contacts";

                break;

            case self::CREATE_ORDER:

                $arr = [
                    'contactId' => isset($params['contactId']) ? $params['contactId'] : '',
                    'amount' => isset($params['amount']) ? $params['amount'] : '',
                    'purpose' => isset($params['purpose']) ? $params['purpose'] : '',
                    'mode' => isset($params['mode']) ? $params['mode'] : '',
                    'narration' => isset($params['narration']) ? $params['narration'] : '',
                    'remark' => isset($params['remark']) ? $params['remark'] : '',
                    'clientRefId' => isset($params['clientRefId']) ? $params['clientRefId'] : ''
                ];

                $str = base64_encode(json_encode($arr));

                $str .= "/v1/service/payout/orders";

                break;
            default:
                $str = "";
                break;
        }


        $str .= "{$clientKey}####{$salt}";

        $str = hash('sha256', $str);

        return $str;
    }
}
