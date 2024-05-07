<?php

return [
    'stripe' => [
        'api-key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret-key' => env('STRIPE_SECRET_KEY')
    ],

    'myfatoorah'=> [
        'api_key' => env('MYFATOORAH_API_KEY'),
        'test_mode' => env('TEST_MODE'),
        'country_iso' => env('COUNTRY_ISO'),
    ],
];
