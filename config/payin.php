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

    'cashfree_url' => env('PAYIN_CASHFREE_URL'),
    'cashfree_app_id' => env('PAYIN_CASHFREE_APP_ID'),
    'cashfree_secret_key' => env('PAYIN_CASHFREE_SECRET_KEY'),
    'cashfree_api_version' => env('PAYIN_CASHFREE_API_VERSION'),
];
