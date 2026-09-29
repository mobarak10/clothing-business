<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'manager', 'customer'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@atelier.test'],
            [
                'name' => 'Store Admin',
                'password' => Hash::make('password'),
                'phone' => '01700000000',
                'is_staff' => true,
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['admin']);

        $customer = User::updateOrCreate(
            ['email' => 'customer@atelier.test'],
            [
                'name' => 'Rafiq Hasan',
                'password' => Hash::make('password'),
                'phone' => '01810000000',
                'marketing_opt_in' => true,
                'is_staff' => false,
                'email_verified_at' => now(),
            ],
        );
        $customer->syncRoles(['customer']);

        if ($customer->addresses()->doesntExist()) {
            Address::factory()->for($customer)->default()->create([
                'first_name' => 'Rafiq',
                'last_name' => 'Hasan',
                'phone' => '01810000000',
                'line1' => 'House 42, Road 11',
                'city' => 'Dhaka',
                'district' => 'Gulshan',
                'postcode' => '1212',
            ]);
        }

        $this->command?->info('  Users: admin@atelier.test / customer@atelier.test (password: "password")');
    }
}
