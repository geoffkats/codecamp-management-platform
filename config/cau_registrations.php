<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Code Academy Uganda Registration API
    |--------------------------------------------------------------------------
    |
    | Used to search website registrations and autofill CodeCamp intake forms.
    |
    */

    'enabled' => env('CAU_REGISTRATION_API_ENABLED', false),

    'base_url' => rtrim((string) env('CAU_REGISTRATION_API_URL', 'https://codeacademyug.org'), '/'),

    'api_key' => env('CAU_REGISTRATION_API_KEY'),

    'timeout' => (int) env('CAU_REGISTRATION_API_TIMEOUT', 10),

];
