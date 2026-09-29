<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCardResource;
use App\Models\Category;
use App\Services\Catalog\CatalogQuery;
use Illuminate\Http\Request;

class CategoryProductController extends Controller
{
    /**
     * Products in a category, filtered and sorted.
     *
     * The path arrives as a wildcard ("clothing/suits/blue-suits") because
     * categories nest three deep and the storefront mirrors that in its URLs.
     */
    public function __invoke(Request $request, string $path)
    {
        $category = Category::active()->where('path', trim($path, '/'))->firstOrFail();

        $query = new CatalogQuery($request, $category);
        $products = $query->paginate();

        return ProductCardResource::collection($products)->additional([
            'meta' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'path' => $category->path,
                ],
                'facets' => $query->facets(),
                'applied_filters' => $query->appliedFilters(),
            ],
        ]);
    }
}
