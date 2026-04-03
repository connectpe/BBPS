<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cashfree Payin URL
    |--------------------------------------------------------------------------
    |
    | This URL is used for processing payin orders through Cashfree.
    | You can set different URLs for different environments (e.g., sandbox and production).
    |
    */

    'cashfree_url' => env('CASHFREE_URL'),
    'cashfree_app_id' => env('CASHFREE_APP_ID'),
    'cashfree_secret_key' => env('CASHFREE_SECRET_KEY'),
    'cashfree_api_version' => env('CASHFREE_API_VERSION'),
];
