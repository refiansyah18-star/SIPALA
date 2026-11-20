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
    | Bridge API v2 (Portal Kampus UIN SSC)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi koneksi ke Bridge API eCampuz untuk autentikasi
    | dan sinkronisasi data profil mahasiswa/dosen/pegawai.
    |
    */

    'bridge' => [
        'base_url' => env('BRIDGE_API_URL', 'https://bridge.uinssc.ac.id'),
        'timeout'  => (int) env('BRIDGE_API_TIMEOUT', 15),
        'v1_token' => env('BRIDGE_API_V1_TOKEN', ''),
    ],

];
