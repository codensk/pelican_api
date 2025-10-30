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

    'booking' => [
        'ip' => '212.20.21.13',
        'defaultClientToken' => env('DEFAULT_CLIENT_TOKEN'),
        'defaultClientContractId' => env('DEFAULT_CLIENT_CONTRACT_ID'),
        'defaultClientEmployeeId' => env('DEFAULT_CLIENT_EMPLOYEE_ID'),
        'notificationEmail' => env('NOTIFICATION_EMAIL'),
        'endpoints' => [
            'priceEndpoint' => 'https://release.busfer.com/api/v1/prices',
            'placeEndpoint' => 'https://release.busfer.com/api/v1/findPlace',
            'servicesEndpoint' => 'https://release.busfer.com/api/pelican/services',
            'clientTokenEndpoint' => 'https://release.busfer.com/api/pelican/clientToken',
            'tripEndpoint' => 'https://release.busfer.com/api/v1/trips',
        ]
    ]

];
