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

    // 'whatsapp' => [
    //     'driver' => env('WHATSAPP_DRIVER', 'generic'),
    //     'api_url' => env('WHATSAPP_API_URL'),
    //     'api_token' => env('WHATSAPP_API_TOKEN'),
    //     'twilio_sid' => env('TWILIO_WHATSAPP_SID'),
    //     'twilio_token' => env('TWILIO_WHATSAPP_TOKEN'),
    //     'from' => env('WHATSAPP_FROM'),
    // ],
    'whatsapp' => [

        'driver' => env('WHATSAPP_DRIVER'),

        'api_url' => env('WHATSAPP_API_URL'),

        'token' => env('WHATSAPP_API_TOKEN'),

        'from' => env('WHATSAPP_FROM'),

        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '92'),

    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
    ],

    'jazzcash' => [
        'endpoint' => env('JAZZCASH_ENDPOINT'),
        'token' => env('JAZZCASH_TOKEN'),
    ],

    'easypaisa' => [
        'endpoint' => env('EASYPAYSA_ENDPOINT'),
        'token' => env('EASYPAYSA_TOKEN'),
    ],
];
