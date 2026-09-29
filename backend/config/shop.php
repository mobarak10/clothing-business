<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    |
    | Single source of truth for the storefront's identity. The Nuxt app reads
    | these through /api/v1/settings so renaming the brand is a one-file change.
    |
    */

    'brand' => [
        'name' => env('APP_NAME', 'Atelier'),
        'tagline' => 'Italian tailoring, made for the modern wardrobe.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Money
    |--------------------------------------------------------------------------
    |
    | Prices are stored as integer minor units (poisha) everywhere -- never
    | floats -- so totals never drift. `divisor` converts to display units.
    |
    */

    'currency' => [
        'code' => env('SHOP_CURRENCY', 'BDT'),
        'symbol' => env('SHOP_CURRENCY_SYMBOL', '৳'),
        'divisor' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    */

    'cart' => [
        // How long an abandoned guest cart survives before cleanup.
        'guest_lifetime_days' => 30,
        'cookie' => 'cart_token',
        'max_quantity_per_line' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Catalog
    |--------------------------------------------------------------------------
    */

    'catalog' => [
        'per_page' => 24,
        'low_stock_threshold' => 3,
    ],

];
