<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SeamlessPayinHelper
{

    public static function generateEasebuzzAccessKey(array $data)
    {
        // $key  = env('EASEBUZZ_KEY');
        // $salt = env('EASEBUZZ_SALT');
        // $baseurl = env('EASEBUZZ_BASE_URL');
        // dd($key, $salt, $baseurl);
        $key = 'XIH4IP6A3F';
        $salt = 'FJ99A4834P';
        

        $txnid = $data['transaction_id'] ?? 'TXN_' . strtoupper(Str::random(15));

        $amount = number_format(
            (float) $data['amount'],
            2,
            '.',
            ''
        );

        $productinfo = 'Payment';

        $firstname = trim($data['firstname'] ?? '');
        $phone     = trim($data['phone'] ?? '');
        $email     = trim($data['email'] ?? '');

        // UDF FIELDS
        $udf1  = '';
        $udf2  = '';
        $udf3  = '';
        $udf4  = '';
        $udf5  = '';
        $udf6  = '';
        $udf7  = '';
        $udf8  = '';
        $udf9  = '';
        $udf10 = '';

     
        // HASH
        $hashString =
            $key . '|' .
            $txnid . '|' .
            $amount . '|' .
            $productinfo . '|' .
            $firstname . '|' .
            $email . '|' .
            $udf1 . '|' .
            $udf2 . '|' .
            $udf3 . '|' .
            $udf4 . '|' .
            $udf5 . '|' .
            $udf6 . '|' .
            $udf7 . '|' .
            $udf8 . '|' .
            $udf9 . '|' .
            $udf10 . '|' .
            $salt;

        $hash = strtolower(
            hash('sha512', $hashString)
        );

      
        // EASEBUZZ INITIATE PAYMENT API

        $url = 'https://pay.easebuzz.in/payment/initiateLink';
        // $url = $baseurl . 'payment/initiateLink';
        // dd($url);

        $payload = [

            'key'         => $key,
            'txnid'       => $txnid,
            'amount'      => $amount,
            'productinfo' => $productinfo,
            'firstname'   => $firstname,
            'phone'       => $phone,
            'email'       => $email,

            'surl' => 'http://127.0.0.1:8000/api/success/page',
            'furl' => 'http://127.0.0.1:8000/api/failure/page',

            'hash' => $hash,

            'udf1'  => $udf1,
            'udf2'  => $udf2,
            'udf3'  => $udf3,
            'udf4'  => $udf4,
            'udf5'  => $udf5,
            'udf6'  => $udf6,
            'udf7'  => $udf7,
            'udf8'  => $udf8,
            'udf9'  => $udf9,
            'udf10' => $udf10,

            'address1' => 'Gomti Nagar',
            'address2' => 'Gomti Nagar',
            'city'     => 'Lucknow',
            'state'    => 'UP',
            'country'  => 'India',
            'zipcode'  => '226010',

            'request_flow' => 'SEAMLESS',
        ];

        $response = Http::asForm()->post($url, $payload);

        $result = $response->json();
        // dd($result);
        // RETURN ACCESS KEY

        if ($response->successful()) {

            return [
                'status'       => true,
                'access_key'   => $result['data'],
                'transaction_id' => $txnid,
                'response'     => $result,
            ];
        }

        return [
            'status'   => false,
            'message'  => $result['msg_desc'] ?? 'Unable to generate access key',
            'response' => $result,
        ];
    }
}