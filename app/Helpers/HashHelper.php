<?php

namespace App\Helpers;


class HashHelper
{
    const CREATE_CONTACT = 'create_contact';


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

            default:
                $str = "";
                break;
        }


        $str .= "{$clientKey}####{$salt}";

        $str = hash('sha256', $str);

        return $str;
    }
}
