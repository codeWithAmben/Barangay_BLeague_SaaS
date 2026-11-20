<?php

return [
    'demo_mode' => (bool) env('BILLING_DEMO_MODE', true),

    'currency' => 'PHP',

    'plans' => [
        'trial' => [
            'name' => 'Free / Trial',
            'price_php' => 0,
            'price_centavos' => 0,
            'duration_months' => 1,
            'description' => '1 tournament, up to 6 purok teams.',
        ],
        'barangay_pro' => [
            'name' => 'Barangay Pro',
            'price_php' => 499,
            'price_centavos' => 49900,
            'duration_months' => 1,
            'description' => 'Unlimited tournaments & teams, live scoreboard, BPG posters.',
        ],
        'municipality' => [
            'name' => 'Municipality / Enterprise',
            'price_php' => 2499,
            'price_centavos' => 249900,
            'duration_months' => 1,
            'description' => 'Multiple concurrent divisions (Juniors, Seniors, 40-Up) & LGU branding.',
        ],
    ],

    'paymongo' => [
        'secret_key' => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),
        'base_url' => env('PAYMONGO_BASE_URL', 'https://api.paymongo.com/v1'),
    ],

    'stripe' => [
        'secret_key' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'base_url' => 'https://api.stripe.com/v1',
    ],
];
