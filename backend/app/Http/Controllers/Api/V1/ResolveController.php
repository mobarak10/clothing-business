<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Http\Request;

class ResolveController extends Controller
{
    /**
     * Tells the storefront what lives at a URL path.
     *
     * The Nuxt catch-all route cannot know whether /clothing/suits is a
     * category or a CMS page, so it asks. One endpoint means adding a new
     * addressable content type never requires a new frontend route.
     */
    public function __invoke(Request $request)
    {
        $path = trim((string) $request->query('path', ''), '/');

        if ($path === '') {
            return response()->json(['message' => 'A path is required.'], 422);
        }

        if ($category = Category::active()->where('path', $path)->first()) {
            return response()->json([
                'type' => 'category',
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'path' => $category->path,
                    'description' => $category->description,
                    'image' => $category->image_path ? asset("storage/{$category->image_path}") : null,

                    'breadcrumbs' => $category->ancestors()
                        ->push($category)
                        ->map(fn (Category $node) => [
                            'label' => $node->name,
                            'url' => "/{$node->path}",
                        ])
                        ->values(),

                    // Refinement links shown above the grid.
                    'children' => $category->children()
                        ->active()
                        ->get()
                        ->map(fn (Category $child) => [
                            'label' => $child->name,
                            'url' => "/{$child->path}",
                        ])
                        ->values(),

                    'seo' => [
                        'title' => $category->meta_title ?: $category->name,
                        'description' => $category->meta_description ?: $category->description,
                    ],
                ],
            ]);
        }

        if ($page = Page::published()->where('slug', $path)->first()) {
            return response()->json([
                'type' => 'page',
                'data' => new PageResource($page),
            ]);
        }

        return response()->json(['message' => 'Not found.'], 404);
    }
}
