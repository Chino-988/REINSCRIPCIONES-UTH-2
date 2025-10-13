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

        'caja' => ['api_key' => env('API_KEY_CAJA')],
    ],

    'caja_api' => [
        'key'     => env('API_KEY_CAJA', ''),     // <--- usa tu .env
        'sources' => ['API', 'CSV', 'EXTENSION'], // metadato informativo
    ],

    'fcm' => [
        'project_id'   => env('FIREBASE_PROJECT_ID'),
        'credentials'  => env('FIREBASE_CREDENTIALS'), // ruta al JSON de credenciales
    ],

        'firebase' => [
        'project_id'       => env('FIREBASE_PROJECT_ID', ''),
        'credentials_file' => env('FIREBASE_CREDENTIALS', null),
        'credentials_json' => env('FIREBASE_CREDENTIALS_JSON', null), // opcional
    ],

];
