<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OptionValue;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The compact product shape used in listings, carousels and search results.
 *
 * @mixin Product
 */
class ProductCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => "/{$this->slug}/p",
            'subtitle' => $this->subtitle,
            'fit' => $this->fit,

            'price' => MoneyResource::make($this->price()),
            'compare_at_price' => MoneyResource::make($this->compareAtPrice()),
            'on_sale' => $this->isOnSale(),

            'is_new_arrival' => (bool) $this->is_new_arrival,

            // Two images: the card swaps to the second on hover.
            'images' => $this->whenLoaded('media', fn () => $this->getMedia('gallery')
                ->take(2)
                ->map(fn ($media) => [
                    'card' => $media->getUrl('card'),
                    'alt' => $media->getCustomProperty('alt') ?? $this->name,
                ])
                ->values(), []),

            // Swatches shown under the card.
            'colors' => $this->whenLoaded('variants', fn () => $this->variants
                ->flatMap->optionValues
                ->filter(fn (OptionValue $value) => $value->optionType->slug === 'color')
                ->unique('id')
                ->map(fn (OptionValue $value) => [
                    'name' => $value->name,
                    'slug' => $value->slug,
                    'hex' => $value->hex,
                ])
                ->values()),

            'in_stock' => $this->whenLoaded(
                'variants',
                fn () => $this->variants->contains(fn ($variant) => $variant->isInStock()),
            ),
        ];
    }
}
