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

    /*
    |--------------------------------------------------------------------------
    | Google Analytics / Tag Manager / Ads
    |--------------------------------------------------------------------------
    |
    | Prefer GTM (GOOGLE_GTM_ID). When set, GA4 is loaded via GTM — do not also
    | hardcode gtag on the page. GOOGLE_GA4_MEASUREMENT_ID is used only when
    | GTM is empty (direct gtag.js fallback).
    |
    | Ads Conversion ID/Label are for GTM tag configuration (and documentation).
    | With the recommended setup, Ads optimizes on the imported GA4 event
    | generate_lead — not a hardcoded AW- conversion snippet in Blade.
    |
    */
    'google' => [
        'gtm_id' => env('GOOGLE_GTM_ID'),
        'ga4_measurement_id' => env('GOOGLE_GA4_MEASUREMENT_ID'),
        'ads_conversion_id' => env('GOOGLE_ADS_CONVERSION_ID'),
        'ads_conversion_label' => env('GOOGLE_ADS_CONVERSION_LABEL'),
    ],

];
