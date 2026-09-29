<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OptionType;
use App\Models\OptionValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OptionSeeder extends Seeder
{
    /**
     * The two axes every garment varies along.
     *
     * Size values are namespaced by garment type ("42" collar vs "W34" waist vs
     * "EU 43") so a single Size option type stays unambiguous -- a shirt 42 and
     * a jacket 42 are different measurements and must not share a slug.
     */
    public function run(): void
    {
        $colors = [
            'Navy' => '#1f2a44',
            'Midnight Blue' => '#191970',
            'Charcoal' => '#36393f',
            'Black' => '#111111',
            'Grey' => '#8b8b8b',
            'Light Grey' => '#c6c6c6',
            'Cream' => '#f2ead8',
            'White' => '#ffffff',
            'Camel' => '#c19a6b',
            'Tobacco' => '#7b4b2a',
            'Olive' => '#5a5f43',
            'Burgundy' => '#6d2434',
            'Sand' => '#d9c9a8',
            'Chocolate' => '#4a3427',
        ];

        $sizes = array_merge(
            ['One Size'],
            // Knitwear, polos, outerwear
            ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            // Tailoring: jackets, suits, coats (EU drop)
            ['44', '46', '48', '50', '52', '54', '56', '58'],
            // Shirts, by collar
            ['37', '38', '39', '40', '41', '42', '43'],
            // Pants, by waist
            ['W30', 'W32', 'W34', 'W36', 'W38', 'W40'],
            // Footwear
            ['EU 39', 'EU 40', 'EU 41', 'EU 42', 'EU 43', 'EU 44', 'EU 45', 'EU 46'],
        );

        $colorType = OptionType::updateOrCreate(
            ['slug' => 'color'],
            ['name' => 'Color', 'presentation' => 'swatch', 'position' => 0],
        );

        $position = 0;
        foreach ($colors as $name => $hex) {
            OptionValue::updateOrCreate(
                ['option_type_id' => $colorType->id, 'slug' => Str::slug($name)],
                ['name' => $name, 'hex' => $hex, 'position' => $position++],
            );
        }

        $sizeType = OptionType::updateOrCreate(
            ['slug' => 'size'],
            ['name' => 'Size', 'presentation' => 'text', 'position' => 1],
        );

        $position = 0;
        foreach ($sizes as $name) {
            OptionValue::updateOrCreate(
                ['option_type_id' => $sizeType->id, 'slug' => Str::slug($name)],
                ['name' => $name, 'hex' => null, 'position' => $position++],
            );
        }

        $this->command?->info(sprintf(
            '  Options: %d colors, %d sizes',
            count($colors),
            count($sizes),
        ));
    }
}
