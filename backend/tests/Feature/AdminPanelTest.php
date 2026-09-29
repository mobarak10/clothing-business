<?php

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\ContentBlocks\ContentBlockResource;
use App\Filament\Resources\Coupons\CouponResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\ShippingMethods\ShippingMethodResource;
use App\Filament\Resources\Stores\StoreResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Product;

/**
 * Smoke tests for the admin panel.
 *
 * A resource whose form or table references a column that does not exist only
 * blows up when the page is actually rendered -- syntax checks and
 * filament:optimize both pass regardless. These tests render each one.
 */
$resources = [
    'products' => ProductResource::class,
    'categories' => CategoryResource::class,
    'orders' => OrderResource::class,
    'customers' => UserResource::class,
    'content blocks' => ContentBlockResource::class,
    'pages' => PageResource::class,
    'stores' => StoreResource::class,
    'appointments' => AppointmentResource::class,
    'coupons' => CouponResource::class,
    'shipping methods' => ShippingMethodResource::class,
    'newsletter subscribers' => NewsletterSubscriberResource::class,
];

foreach ($resources as $label => $resource) {
    it("renders the {$label} list page", function () use ($resource) {
        actingAsStaff();

        $this->get($resource::getUrl('index'))->assertSuccessful();
    });
}

foreach ($resources as $label => $resource) {
    $pages = $resource::getPages();

    if (! isset($pages['create'])) {
        continue;
    }

    it("renders the {$label} create page", function () use ($resource) {
        actingAsStaff();

        $this->get($resource::getUrl('create'))->assertSuccessful();
    });
}

it('renders a product edit page with its variants', function () {
    actingAsStaff();

    $product = Product::factory()
        ->has(App\Models\ProductVariant::factory()->count(3), 'variants')
        ->create();

    $this->get(ProductResource::getUrl('edit', ['record' => $product]))
        ->assertSuccessful()
        ->assertSee($product->name);
});

it('does not offer an order create route', function () {
    expect(OrderResource::getPages())->not->toHaveKey('create')
        ->and(OrderResource::canCreate())->toBeFalse();
});

it('keeps customers out of the admin panel', function () {
    $customer = actingAsCustomer();

    expect($customer->canAccessPanel(Filament\Facades\Filament::getPanel('admin')))
        ->toBeFalse();
});

it('lets staff into the admin panel', function () {
    $staff = actingAsStaff();

    expect($staff->canAccessPanel(Filament\Facades\Filament::getPanel('admin')))
        ->toBeTrue();
});

it('redirects guests to the login screen', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});
