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

    'client_id' => env('VERIFICATION_CLIENT_ID', ''),
    'client_secret' => env('VERIFICATION_CLIENT_SECRET', ''),

];
