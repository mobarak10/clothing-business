<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One node of the mega-menu tree, with its children nested beneath it.
 *
 * @mixin Category
 */
class NavigationCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $this->path,
            'url' => "/{$this->path}",
            'is_featured' => (bool) $this->is_featured,
            'image' => $this->image_path ? asset("storage/{$this->image_path}") : null,
            'children' => self::collection(
                $this->whenLoaded('children', fn () => $this->children->where('is_active', true))
            ),
        ];
    }
}
