<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Category;
use App\Models\OptionValue;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Builds a listing page: the filtered, sorted product grid and the facets
 * shown beside it.
 *
 * Kept out of the controller because the category page, the search page and
 * the "all products" page all need identical behaviour.
 */
class CatalogQuery
{
    private const SORTS = [
        'newest' => ['published_at', 'desc'],
        'price_asc' => ['base_price', 'asc'],
        'price_desc' => ['base_price', 'desc'],
        'name_asc' => ['name', 'asc'],
    ];

    public function __construct(
        private readonly Request $request,
        private readonly ?Category $category = null,
        private readonly ?string $search = null,
    ) {}

    public function paginate(): LengthAwarePaginator
    {
        $perPage = min(
            (int) $this->request->integer('per_page', (int) config('shop.catalog.per_page', 24)),
            60,
        );

        return $this->filtered()
            ->with(['media', 'variants.optionValues.optionType'])
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Facets for the sidebar.
     *
     * Computed from the category's product set rather than the filtered one,
     * so a shopper who picks Navy can still see that Black exists and switch
     * to it. Counts would be more informative but would need one query per
     * facet; that is a refinement for when the catalog is large enough to
     * warrant it.
     */
    public function facets(): array
    {
        $productIds = $this->base()->pluck('products.id');

        if ($productIds->isEmpty()) {
            return ['options' => [], 'fits' => [], 'price' => null];
        }

        $optionValues = OptionValue::query()
            ->with('optionType')
            ->whereHas('variants', fn (Builder $query) => $query
                ->whereIn('product_variants.product_id', $productIds)
                ->where('product_variants.is_active', true))
            ->get();

        $options = $optionValues
            ->groupBy(fn (OptionValue $value) => $value->optionType->slug)
            ->map(fn (Collection $values, string $slug) => [
                'slug' => $slug,
                'name' => $values->first()->optionType->name,
                'presentation' => $values->first()->optionType->presentation,
                'values' => $values->sortBy('position')
                    ->map(fn (OptionValue $value) => [
                        'name' => $value->name,
                        'slug' => $value->slug,
                        'hex' => $value->hex,
                    ])
                    ->values(),
            ])
            ->values();

        $prices = Product::whereIn('id', $productIds)
            ->selectRaw('MIN(base_price) AS min_price, MAX(base_price) AS max_price')
            ->first();

        return [
            'options' => $options,
            'fits' => Product::whereIn('id', $productIds)
                ->whereNotNull('fit')
                ->distinct()
                ->orderBy('fit')
                ->pluck('fit')
                ->values(),
            'price' => [
                'min' => (int) $prices->min_price,
                'max' => (int) $prices->max_price,
            ],
        ];
    }

    /** The product set before any shopper-chosen filters. */
    private function base(): Builder
    {
        $query = Product::query()->active();

        if ($this->category) {
            $ids = $this->category->descendantAndSelfIds();

            $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $ids));
        }

        if ($this->search) {
            $term = str_replace(['%', '_'], ['\%', '\_'], $this->search);

            $query->where(fn (Builder $q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('subtitle', 'like', "%{$term}%")
                ->orWhere('sku_root', 'like', "%{$term}%")
                ->orWhere('composition', 'like', "%{$term}%"));
        }

        return $query;
    }

    private function filtered(): Builder
    {
        $query = $this->base();

        $this->applyOptionFilters($query);
        $this->applyPriceFilter($query);
        $this->applyFitFilter($query);
        $this->applyStockFilter($query);
        $this->applySort($query);

        return $query;
    }

    /**
     * Option filters, e.g. ?color[]=navy&size[]=50
     *
     * Values within one option type are OR'd; different types are AND'd
     * against a SINGLE variant. That last part matters: asking for navy in
     * size 50 must not surface a product that has navy only in size 46.
     */
    private function applyOptionFilters(Builder $query): void
    {
        $groups = collect($this->request->query())
            ->only(OptionValue::query()
                ->with('optionType')
                ->get()
                ->pluck('optionType.slug')
                ->unique()
                ->all())
            ->map(fn ($value) => array_filter((array) $value))
            ->filter();

        if ($groups->isEmpty()) {
            return;
        }

        $query->whereHas('variants', function (Builder $variantQuery) use ($groups): void {
            $variantQuery->where('is_active', true);

            foreach ($groups as $typeSlug => $valueSlugs) {
                $variantQuery->whereHas('optionValues', fn (Builder $q) => $q
                    ->whereIn('option_values.slug', $valueSlugs)
                    ->whereHas('optionType', fn (Builder $t) => $t->where('slug', $typeSlug)));
            }
        });
    }

    /** Bounds arrive in major units (taka); the column stores minor units. */
    private function applyPriceFilter(Builder $query): void
    {
        $divisor = (int) config('shop.currency.divisor', 100);

        if ($min = $this->request->query('price_min')) {
            $query->where('base_price', '>=', (int) round((float) $min * $divisor));
        }

        if ($max = $this->request->query('price_max')) {
            $query->where('base_price', '<=', (int) round((float) $max * $divisor));
        }
    }

    private function applyFitFilter(Builder $query): void
    {
        $fits = array_filter((array) $this->request->query('fit', []));

        if ($fits) {
            $query->whereIn('fit', $fits);
        }
    }

    private function applyStockFilter(Builder $query): void
    {
        if ($this->request->boolean('in_stock')) {
            $query->inStock();
        }
    }

    private function applySort(Builder $query): void
    {
        $sort = (string) $this->request->query('sort', 'newest');

        [$column, $direction] = self::SORTS[$sort] ?? self::SORTS['newest'];

        // A deterministic tiebreak keeps pagination stable when many rows
        // share a value (every product published the same day, say).
        $query->orderBy($column, $direction)->orderBy('id');
    }

    /** Echoed back so the storefront can render "active filter" chips. */
    public function appliedFilters(): array
    {
        return array_filter([
            'sort' => $this->request->query('sort', 'newest'),
            'price_min' => $this->request->query('price_min'),
            'price_max' => $this->request->query('price_max'),
            'fit' => array_filter((array) $this->request->query('fit', [])),
            'in_stock' => $this->request->boolean('in_stock') ?: null,
            'color' => array_filter((array) $this->request->query('color', [])),
            'size' => array_filter((array) $this->request->query('size', [])),
        ]);
    }
}
