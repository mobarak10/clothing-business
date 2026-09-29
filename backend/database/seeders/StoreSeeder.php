<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /** Standard boutique hours; Friday opens late. */
    private const HOURS = [
        'mon' => ['10:00', '20:00'],
        'tue' => ['10:00', '20:00'],
        'wed' => ['10:00', '20:00'],
        'thu' => ['10:00', '20:00'],
        'fri' => ['15:00', '20:30'],
        'sat' => ['10:00', '20:30'],
        'sun' => ['10:00', '20:00'],
    ];

    public function run(): void
    {
        $stores = [
            [
                'name' => 'Gulshan Flagship',
                'slug' => 'gulshan-flagship',
                'line1' => 'Road 11, Gulshan 1',
                'city' => 'Dhaka',
                'district' => 'Gulshan',
                'postcode' => '1212',
                'phone' => '+880 9613 100200',
                'email' => 'gulshan@atelier.test',
                'latitude' => 23.7806,
                'longitude' => 90.4152,
                'offers_made_to_measure' => true,
                'position' => 0,
            ],
            [
                'name' => 'Banani Boutique',
                'slug' => 'banani-boutique',
                'line1' => 'Kemal Ataturk Avenue, Banani',
                'city' => 'Dhaka',
                'district' => 'Banani',
                'postcode' => '1213',
                'phone' => '+880 9613 100201',
                'email' => 'banani@atelier.test',
                'latitude' => 23.7937,
                'longitude' => 90.4066,
                'offers_made_to_measure' => true,
                'position' => 1,
            ],
            [
                'name' => 'Dhanmondi',
                'slug' => 'dhanmondi',
                'line1' => 'Satmasjid Road, Dhanmondi 27',
                'city' => 'Dhaka',
                'district' => 'Dhanmondi',
                'postcode' => '1209',
                'phone' => '+880 9613 100202',
                'email' => 'dhanmondi@atelier.test',
                'latitude' => 23.7461,
                'longitude' => 90.3742,
                'offers_made_to_measure' => false,
                'position' => 2,
            ],
            [
                'name' => 'Chattogram',
                'slug' => 'chattogram',
                'line1' => 'CDA Avenue, Nasirabad',
                'city' => 'Chattogram',
                'district' => 'Nasirabad',
                'postcode' => '4000',
                'phone' => '+880 9613 100203',
                'email' => 'chattogram@atelier.test',
                'latitude' => 22.3606,
                'longitude' => 91.8210,
                'offers_made_to_measure' => true,
                'position' => 3,
            ],
            [
                'name' => 'Sylhet',
                'slug' => 'sylhet',
                'line1' => 'Zindabazar Main Road',
                'city' => 'Sylhet',
                'district' => 'Zindabazar',
                'postcode' => '3100',
                'phone' => '+880 9613 100204',
                'email' => 'sylhet@atelier.test',
                'latitude' => 24.8949,
                'longitude' => 91.8687,
                'offers_made_to_measure' => false,
                'position' => 4,
            ],
        ];

        foreach ($stores as $store) {
            Store::updateOrCreate(
                ['slug' => $store['slug']],
                $store + [
                    'country_code' => 'BD',
                    'opening_hours' => self::HOURS,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('  Stores: '.count($stores).' boutiques');
    }
}
