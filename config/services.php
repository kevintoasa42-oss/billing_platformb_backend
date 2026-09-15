<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'sri' => [
        'mode' => env('SRI_MODE', 'mock'),
        'invoice_xsd_version' => env('SRI_INVOICE_XSD_VERSION', '2.1.0'),
        'invoice_xsd' => env('SRI_INVOICE_XSD', resource_path('sri/xsd/factura_V2.1.0.xsd')),
        'invoice_xsd_sha256' => env('SRI_INVOICE_XSD_SHA256', '5f2c37bc1a58bb40e8bbbc366cabe05d5dc199598aeea1561137370f8bd4eace'),
        'wait_seconds' => (int) env('SRI_WAIT_SECONDS', 5),
        'authorization_max_attempts' => (int) env('SRI_AUTHORIZATION_MAX_ATTEMPTS', 5),
        'wsdl' => [
            'reception' => [
                '1' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
                '2' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            ],
            'authorization' => [
                '1' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
                '2' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
            ],
        ],
    ],

    'openbao' => [
        'address' => env('OPENBAO_ADDR'),
        'transit_mount' => env('OPENBAO_TRANSIT_MOUNT', 'transit'),
        'ca_certificate' => env('OPENBAO_CACERT'),
        'client_certificate' => env('OPENBAO_CLIENT_CERT'),
        'client_key' => env('OPENBAO_CLIENT_KEY'),
        'credentials_directory' => env('OPENBAO_CREDENTIALS_DIRECTORY'),
        'connect_timeout_seconds' => max(1, min(10, (int) env('OPENBAO_CONNECT_TIMEOUT_SECONDS', 3))),
        'timeout_seconds' => max(2, min(30, (int) env('OPENBAO_TIMEOUT_SECONDS', 10))),
    ],

];
