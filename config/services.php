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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

    'whatsapp' => [
        'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET'),
    ],

    'elevenza' => [
        'base_url' => env('ELEVENZA_API_URL'),
        'api_key' => env('ELEVENZA_API_KEY'),
        'sender' => env('ELEVENZA_SENDER_ID'),
    ],

    'facebook_leads' => [
        'verify_token' => env('FACEBOOK_LEADS_VERIFY_TOKEN'),
        'app_secret' => env('FACEBOOK_LEADS_APP_SECRET'),
        'page_access_token' => env('FACEBOOK_LEADS_PAGE_ACCESS_TOKEN'),
    ],

    'google_ads_leads' => [
        'webhook_secret' => env('GOOGLE_ADS_LEADS_WEBHOOK_SECRET'),
    ],

];
