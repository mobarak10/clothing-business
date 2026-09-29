<?php

declare(strict_types=1);

namespace App\Observers;

use App\Http\Controllers\Api\V1\NavigationController;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

/**
 * Keeps the cached mega-menu honest.
 *
 * The navigation tree is cached forever because it changes only when someone
 * edits a category -- which is exactly what this observer watches for. Path
 * rebuilding itself lives in the Category model's own save hooks, since it
 * must run inside the save rather than after it.
 */
class CategoryObserver
{
    public function saved(Category $category): void
    {
        $this->flush();
    }

    public function deleted(Category $category): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(NavigationController::CACHE_KEY);
    }
}
