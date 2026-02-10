<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment (UAT / PROD)
    |--------------------------------------------------------------------------
    */
    'env' => env('MOBIKWIK_ENV', 'uat'),

    /*
    |--------------------------------------------------------------------------
    | Base URLs
    |--------------------------------------------------------------------------
    */
    'base_url' =>env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com'),

    /*
    |--------------------------------------------------------------------------
    | Encryption / Security
    |--------------------------------------------------------------------------
    */
    
    'key_version' => env('MOBIKWIK_KEY_VERSION', '1.0'),
    'payment_account_info' => env('MOBIKWIK_PAYMENT_ACCOUNT_INFO'), 
    'public_key' => storage_path(env('MOBIKWIK_PUBLIC_KEY_PATH','app/public/keys/mobikwik_public_key.pem')),

    /*
    |--------------------------------------------------------------------------
    | Credentials (if required later)
    |--------------------------------------------------------------------------
    */
   
    'client_id'     => env('MOBIKWIK_CLIENT_ID'),
    'client_secret' => env('MOBIKWIK_CLIENT_SECRET'),
 
    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    'timeout' => env('MOBIKWIK_TIMEOUT', 30),
];
