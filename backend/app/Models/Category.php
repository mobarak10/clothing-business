<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(CategoryObserver::class)]
class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // `path` is what storefront URLs resolve against, so it must always
        // agree with the tree. Rebuilding on save keeps the two in step; moving
        // a branch also rewrites everything beneath it.
        static::saving(function (Category $category): void {
            $category->slug ??= Str::slug($category->name);
            $category->path = $category->buildPath();
        });

        static::saved(function (Category $category): void {
            if ($category->wasChanged('path')) {
                $category->refreshDescendantPaths();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('position')
            ->orderByPivot('position');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Tree helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Every category id from here down, so a listing page can show products
     * filed under child categories too.
     *
     * @return list<int>
     */
    public function descendantAndSelfIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->descendantAndSelfIds());
        }

        return $ids;
    }

    /** Ancestor chain, root first -- used to build breadcrumbs. */
    public function ancestors(): \Illuminate\Support\Collection
    {
        $chain = collect();
        $node = $this->parent;

        while ($node) {
            $chain->prepend($node);
            $node = $node->parent;
        }

        return $chain;
    }

    public function depth(): int
    {
        return substr_count($this->path ?? '', '/');
    }

    private function buildPath(): string
    {
        if (! $this->parent_id) {
            return $this->slug;
        }

        // relationLoaded guards against a stale in-memory parent after a move.
        $parent = $this->relationLoaded('parent') && $this->parent?->getKey() === $this->parent_id
            ? $this->parent
            : self::find($this->parent_id);

        return $parent ? "{$parent->path}/{$this->slug}" : $this->slug;
    }

    private function refreshDescendantPaths(): void
    {
        foreach ($this->children()->get() as $child) {
            // save() re-enters the saving hook, which rebuilds the child's path
            // from this node's new one and recurses further down.
            $child->save();
        }
    }
}
