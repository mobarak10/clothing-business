<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Services\Catalog\CatalogQuery;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return ProductCardResource::collection([])->additional([
                'meta' => [
                    'query' => $term,
                    'message' => 'Enter at least two characters.',
                ],
            ]);
        }

        $query = new CatalogQuery($request, category: null, search: $term);
        $products = $query->paginate();

        return ProductCardResource::collection($products)->additional([
            'meta' => [
                'query' => $term,
                'facets' => $query->facets(),
                'applied_filters' => $query->appliedFilters(),
            ],
        ]);
    }
}
