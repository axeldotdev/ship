<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'workos' => [
        'client_id' => env('WORKOS_CLIENT_ID'),
        'secret' => env('WORKOS_API_KEY'),
        'redirect_url' => env('WORKOS_REDIRECT_URL'),
    ],

];
