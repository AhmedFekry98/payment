<?php

return [
    'stripe' => [
        'api-key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret-key' => env('STRIPE_SECRET_KEY')
    ],
];
