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

    'whatsapp' => [
        'number' => env('WHATSAPP_NUMBER', ''),
    ],

    'twilio' => [
        'account_sid'   => env('TWILIO_ACCOUNT_SID'),
        'auth_token'    => env('TWILIO_AUTH_TOKEN'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
    ],

    'payment' => [
        'online_driver' => env('PAYMENT_ONLINE_DRIVER', 'cinetpay'),
    ],

    // Clé partagée protégeant les endpoints /api/can/* appelés par Twilio Studio.
    // Laisser vide tant que la clé n'est pas posée dans le flow Twilio (fail-open).
    'can_api_key' => env('CAN_API_KEY', ''),

    'cinetpay' => [
        'api_key'    => env('CINETPAY_API_KEY', ''),
        'site_id'    => env('CINETPAY_SITE_ID', ''),
        'notify_url' => env('CINETPAY_NOTIFY_URL', ''),
    ],

];
