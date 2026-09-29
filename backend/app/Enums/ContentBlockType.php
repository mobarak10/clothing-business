<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Homepage section types.
 *
 * Each case maps to a Vue component in the storefront
 * (frontend/app/components/blocks/) and to a payload shape edited in Filament.
 */
enum ContentBlockType: string implements HasLabel
{
    /** Full-bleed campaign image with an overlaid headline and CTA. */
    case Hero = 'hero';

    /** Editorial grid of styled looks linking to products. */
    case LookGrid = 'look_grid';

    /** Row of category entry points -- "Sneakers", "Leather Jackets". */
    case CategoryTiles = 'category_tiles';

    /** Image beside text; used for Made to Measure and Care. */
    case EditorialSplit = 'editorial_split';

    /** Horizontally scrolling strip of products from one category. */
    case ProductCarousel = 'product_carousel';

    /** Store finder / appointment prompt. */
    case StoreFinder = 'store_finder';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hero => 'Campaign hero',
            self::LookGrid => 'Look grid',
            self::CategoryTiles => 'Category tiles',
            self::EditorialSplit => 'Editorial split',
            self::ProductCarousel => 'Product carousel',
            self::StoreFinder => 'Store finder',
        };
    }
}
