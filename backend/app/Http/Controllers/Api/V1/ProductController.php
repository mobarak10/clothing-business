<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Http\Resources\ProductDetailResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::query()
            ->active()
            ->with([
                'media',
                'variants' => fn ($query) => $query->where('is_active', true),
                'variants.optionValues.optionType',
                'categories',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return (new ProductDetailResource($product))->additional([
            'meta' => ['related' => ProductCardResource::collection($this->related($product))],
        ]);
    }

    /**
     * Other pieces from the nearest category that has enough of them.
     *
     * Starts at the most specific category ("other blue suits" beats "other
     * clothing") and climbs the ancestry until it finds something to show.
     * Without the climb, a product that is the only item in a leaf category
     * renders an empty carousel.
     */
    private function related(Product $product, int $limit = 8)
    {
        $deepest = $product->categories
            ->sortByDesc(fn ($category) => substr_count($category->path, '/'))
            ->first();

        if (! $deepest) {
            return collect();
        }

        // Most specific first, then each ancestor in turn.
        $candidates = collect([$deepest])->concat($deepest->ancestors()->reverse());

        foreach ($candidates as $category) {
            $ids = $category->descendantAndSelfIds();

            $related = Product::query()
                ->active()
                ->whereKeyNot($product->getKey())
                ->whereHas('categories', fn (Builder $query) => $query->whereIn('categories.id', $ids))
                ->with(['media', 'variants.optionValues.optionType'])
                ->inRandomOrder()
                ->limit($limit)
                ->get();

            // Two is the point at which a row stops looking like a mistake.
            if ($related->count() >= 2) {
                return $related;
            }
        }

        return collect();
    }
}
