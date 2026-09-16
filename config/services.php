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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GeniusPay — encaissement en ligne (Wave, Orange, MTN, Moov, carte)
    |--------------------------------------------------------------------------
    |
    | `secret` et `webhook_secret` ne doivent jamais sortir du serveur. Le
    | secret webhook (whsec_...) n'est affiché qu'une fois, à la création du
    | webhook chez GeniusPay : il n'est plus récupérable ensuite.
    |
    */

    'geniuspay' => [
        'base_url'       => env('GENIUSPAY_BASE_URL', 'https://geniuspay.ci/api/v1/merchant'),
        'key'            => env('GENIUSPAY_API_KEY'),
        'secret'         => env('GENIUSPAY_API_SECRET'),
        'webhook_secret' => env('GENIUSPAY_WEBHOOK_SECRET'),
        'environment'    => env('GENIUSPAY_ENVIRONMENT', 'sandbox'),
    ],

];
