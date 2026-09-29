<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests hit the database; RefreshDatabase wraps each one in a
| transaction against the clothing_test schema configured in phpunit.xml.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/** A signed-in staff member who can reach the Filament panel. */
function actingAsStaff(): App\Models\User
{
    $user = App\Models\User::factory()->create(['is_staff' => true]);

    test()->actingAs($user);

    return $user;
}

/** A signed-in shopper, who must never reach the admin. */
function actingAsCustomer(): App\Models\User
{
    $user = App\Models\User::factory()->create(['is_staff' => false]);

    test()->actingAs($user);

    return $user;
}
