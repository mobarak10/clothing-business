<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OptionValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The full product page payload.
 *
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'url' => "/{$this->slug}/p",
            'sku_root' => $this->sku_root,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'composition' => $this->composition,
            'care_instructions' => $this->care_instructions,
            'fit' => $this->fit,

            'price' => MoneyResource::make($this->price()),
            'compare_at_price' => MoneyResource::make($this->compareAtPrice()),
            'on_sale' => $this->isOnSale(),

            'is_new_arrival' => (bool) $this->is_new_arrival,
            'in_stock' => $this->totalStock() > 0,

            'images' => $this->getMedia('gallery')->map(fn ($media) => [
                'thumb' => $media->getUrl('thumb'),
                'card' => $media->getUrl('card'),
                'zoom' => $media->getUrl('zoom'),
                'alt' => $media->getCustomProperty('alt') ?? $this->name,
                // Lets the gallery filter down to one colourway.
                'color' => $media->getCustomProperty('color'),
            ])->values(),

            // Every axis this product varies along, with the values actually
            // used by its variants -- so the PDP never renders a size that
            // does not exist for this garment.
            'options' => $this->optionAxes(),

            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(
                fn (ProductVariant $variant) => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'options' => $variant->optionValues->mapWithKeys(fn (OptionValue $value) => [
                        $value->optionType->slug => $value->slug,
                    ]),
                    'price' => MoneyResource::make($variant->price()),
                    'compare_at_price' => MoneyResource::make($variant->compareAtPrice()),
                    'in_stock' => $variant->isInStock(),
                    'low_stock' => $variant->isLowStock(),
                    'stock_quantity' => $variant->stock_quantity,
                ]
            )->values()),

            'categories' => $this->whenLoaded('categories', fn () => $this->categories->map(fn ($category) => [
                'name' => $category->name,
                'path' => $category->path,
                'url' => "/{$category->path}",
            ])->values()),

            'breadcrumbs' => $this->breadcrumbs(),

            'seo' => [
                'title' => $this->meta_title ?: $this->name,
                'description' => $this->meta_description ?: str($this->description)->limit(155)->toString(),
            ],
        ];
    }

    /**
     * Option types in display order, each with the distinct values this
     * product's variants use.
     */
    private function optionAxes(): array
    {
        if (! $this->relationLoaded('variants')) {
            return [];
        }

        return $this->variants
            ->flatMap->optionValues
            ->groupBy(fn (OptionValue $value) => $value->optionType->slug)
            ->map(fn ($values, $slug) => [
                'slug' => $slug,
                'name' => $values->first()->optionType->name,
                'presentation' => $values->first()->optionType->presentation,
                'values' => $values->unique('id')
                    ->sortBy('position')
                    ->map(fn (OptionValue $value) => [
                        'id' => $value->id,
                        'name' => $value->name,
                        'slug' => $value->slug,
                        'hex' => $value->hex,
                    ])
                    ->values(),
            ])
            ->values()
            ->all();
    }

    /**
     * Built from the deepest category, which gives the most specific trail
     * (Clothing > Suits > Blue Suits rather than just Clothing).
     */
    private function breadcrumbs(): array
    {
        if (! $this->relationLoaded('categories')) {
            return [];
        }

        $deepest = $this->categories->sortByDesc(fn ($category) => substr_count($category->path, '/'))->first();

        if (! $deepest) {
            return [];
        }

        return $deepest->ancestors()
            ->push($deepest)
            ->map(fn ($category) => [
                'label' => $category->name,
                'url' => "/{$category->path}",
            ])
            ->values()
            ->all();
    }
}
