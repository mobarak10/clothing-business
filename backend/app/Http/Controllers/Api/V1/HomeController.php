<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\ContentBlockType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContentBlockResource;
use App\Models\Category;
use App\Models\ContentBlock;
use App\Models\Product;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * The homepage, as an ordered list of blocks.
     *
     * Blocks that reference products get them resolved here rather than
     * leaving the storefront to make a second round of requests -- the
     * homepage should render in one call.
     */
    public function __invoke()
    {
        $blocks = ContentBlock::live('homepage')->get();

        $blocks->each(function (ContentBlock $block): void {
            if ($block->type === ContentBlockType::ProductCarousel) {
                $block->resolvedProducts = $this->productsFor($block);
            }
        });

        return ContentBlockResource::collection($blocks);
    }

    /** Resolves a carousel block's `source` into actual products. */
    private function productsFor(ContentBlock $block): Collection
    {
        $payload = $block->payload;
        $limit = (int) ($payload['limit'] ?? 12);

        $query = Product::query()
            ->active()
            ->with(['media', 'variants.optionValues.optionType']);

        match ($payload['source'] ?? 'new_arrivals') {
            'new_arrivals' => $query->where('is_new_arrival', true)->latest('published_at'),

            'on_sale' => $query->whereNotNull('compare_at_price')
                ->whereColumn('compare_at_price', '>', 'base_price'),

            'category' => $query->whereHas('categories', function ($categoryQuery) use ($payload) {
                $category = Category::where('path', $payload['category_path'] ?? '')->first();

                $categoryQuery->whereIn(
                    'categories.id',
                    $category ? $category->descendantAndSelfIds() : [],
                );
            }),

            default => $query->latest('published_at'),
        };

        return $query->limit($limit)->get();
    }
}
