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
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'log'), // 'log', 'mimsms', 'twilio'
    ],

    /*
    |--------------------------------------------------------------------------
    | MiMSMS API Configuration (Bangladesh)
    |--------------------------------------------------------------------------
    |
    | Get your credentials from https://www.mimsms.com/
    | Login to your account > Developer Option
    |
    */

    'mimsms' => [
        'username' => env('MIMSMS_USERNAME'), // Your email/username
        'api_key' => env('MIMSMS_API_KEY'), // Your API key from Developer Option
        'sender_name' => env('MIMSMS_SENDER_NAME', 'GCL'), // Registered sender ID
        'transaction_type' => env('MIMSMS_TRANSACTION_TYPE', 'T'), // T=Transactional, P=Promotional
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

];
