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
        'username'          => env('MIMSMS_USERNAME'),
        'api_key'           => env('MIMSMS_API_KEY'),
        'sender_name'       => env('MIMSMS_SENDER_NAME', 'GCL'),
        'transaction_type'  => env('MIMSMS_TRANSACTION_TYPE', 'T'), // 'T' or 'text'
    ],





];
