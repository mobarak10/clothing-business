<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\CategoryProductController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\NavigationController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ResolveController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Diagnostics & settings
    |--------------------------------------------------------------------------
    */

    Route::get('health', fn () => [
        'status' => 'ok',
        'app' => config('shop.brand.name'),
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'time' => now()->toIso8601String(),
    ]);

    Route::get('settings', fn () => [
        'brand' => config('shop.brand'),
        'currency' => config('shop.currency'),
        'payment_methods' => collect(config('payment.gateways'))
            ->map(fn (array $gateway, string $key) => ['key' => $key, 'label' => $gateway['label']])
            ->values(),
    ]);

    /*
    |--------------------------------------------------------------------------
    | Storefront chrome
    |--------------------------------------------------------------------------
    */

    Route::get('navigation', NavigationController::class);
    Route::get('home', HomeController::class);

    // Tells the Nuxt catch-all route whether a path is a category or a page.
    Route::get('resolve', ResolveController::class);

    /*
    |--------------------------------------------------------------------------
    | Catalog
    |--------------------------------------------------------------------------
    */

    Route::get('search', SearchController::class);
    Route::get('products/{slug}', [ProductController::class, 'show']);

    // Wildcard: category paths nest up to three segments deep.
    Route::get('categories/{path}/products', CategoryProductController::class)
        ->where('path', '.*');

    /*
    |--------------------------------------------------------------------------
    | Boutiques
    |--------------------------------------------------------------------------
    */

    Route::get('stores', [StoreController::class, 'index']);
    Route::get('stores/{slug}', [StoreController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Forms
    |--------------------------------------------------------------------------
    */

    Route::post('newsletter', [NewsletterController::class, 'store']);
    Route::delete('newsletter', [NewsletterController::class, 'destroy']);
    Route::post('appointments', [AppointmentController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', fn (Request $request) => $request->user());
    });

});
