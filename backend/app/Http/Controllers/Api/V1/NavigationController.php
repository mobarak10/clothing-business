<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NavigationCategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class NavigationController extends Controller
{
    /** Cleared by CategoryObserver whenever the tree changes. */
    public const CACHE_KEY = 'navigation.tree';

    /**
     * The mega-menu tree.
     *
     * Hit on every server-rendered page, and it changes only when a
     * merchandiser edits a category -- so it is cached until invalidated
     * rather than rebuilt per request.
     */
    public function __invoke()
    {
        $tree = Cache::rememberForever(self::CACHE_KEY, function () {
            $roots = Category::query()
                ->active()
                ->roots()
                ->with(['children' => fn ($query) => $query->active()->with([
                    'children' => fn ($query) => $query->active(),
                ])])
                ->orderBy('position')
                ->get();

            return NavigationCategoryResource::collection($roots)->resolve();
        });

        return response()->json(['data' => $tree]);
    }
}
