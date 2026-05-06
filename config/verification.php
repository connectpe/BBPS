<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verification API Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are used for authenticating with the external verification API.
    | Make sure to set these in your .env file for security.
    |
    */

    'client_id' => env('CASHFREE_CLIENT_ID', ''),
    'client_secret' => env('CASHFREE_CLIENT_SECRET', ''),
    'bankAccount_url' => env('CASHFREE_BANKACCOUNT_URL'),
];
