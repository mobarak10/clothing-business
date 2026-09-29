<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Gateway
    |--------------------------------------------------------------------------
    |
    | Which driver checkout uses. Every driver implements the same
    | App\Services\Payment\PaymentGateway contract, so swapping providers is a
    | config change rather than a checkout rewrite. "cod" needs no credentials
    | and is what local development runs against.
    |
    */

    'default' => env('PAYMENT_DRIVER', 'cod'),

    /*
    |--------------------------------------------------------------------------
    | Gateways
    |--------------------------------------------------------------------------
    */

    'gateways' => [

        'cod' => [
            'driver' => \App\Services\Payment\CashOnDeliveryGateway::class,
            'label' => 'Cash on Delivery',
        ],

        'sslcommerz' => [
            'driver' => \App\Services\Payment\SslCommerzGateway::class,
            'label' => 'Card / Mobile Banking',
            'store_id' => env('SSLCOMMERZ_STORE_ID'),
            'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
            'sandbox' => env('SSLCOMMERZ_SANDBOX', true),

            // SSLCommerz exposes the same three endpoints on both hosts; only
            // the base differs between sandbox and live.
            'endpoints' => [
                'sandbox' => 'https://sandbox.sslcommerz.com',
                'live' => 'https://securepay.sslcommerz.com',
            ],
        ],

        'bkash' => [
            'driver' => \App\Services\Payment\BkashGateway::class,
            'label' => 'bKash',
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
            'sandbox' => env('BKASH_SANDBOX', true),

            'endpoints' => [
                'sandbox' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta',
                'live' => 'https://tokenized.pay.bka.sh/v1.2.0-beta',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect Targets
    |--------------------------------------------------------------------------
    |
    | Where the gateway sends the shopper back to. These point at the Nuxt
    | storefront, not the API, so the customer never sees a bare JSON response.
    |
    */

    'redirects' => [
        'success' => env('FRONTEND_URL', 'http://localhost:3000').'/checkout/confirmation',
        'fail' => env('FRONTEND_URL', 'http://localhost:3000').'/checkout/failed',
        'cancel' => env('FRONTEND_URL', 'http://localhost:3000').'/cart',
    ],

];
