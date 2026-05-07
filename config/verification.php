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
    'ifsc_url' => env('CASHFREE_IFSC_URL'),
    'pan_url' => env('CASHFREE_PAN_URL'),
    'gstin_url' => env('CASHFREE_GSTIN_URL'),
    'cin_url' => env('CASHFREE_CIN_URL'),
    'aadharMasking_url' => env('CASHFREE_AADHAR_MASKING_URL'),
];
