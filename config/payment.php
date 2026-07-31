<?php

return [
    'gateway' => env('PAYMENT_GATEWAY', 'simulation'),

    'currency' => env('PAYMENT_CURRENCY', 'usd'),

    'failure_rate' => (float) env('PAYMENT_FAILURE_RATE', 0),

    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'test_card' => env('STRIPE_TEST_CARD', 'tok_visa'),
        'url' => 'https://api.stripe.com/v1/charges',
    ],
];
