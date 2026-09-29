<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'stock_quantity' => 'integer',
            'position' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(OptionValue::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('stock_quantity', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    */

    /** The variant's own price, falling back to the product's base price. */
    public function price(): Money
    {
        return Money::of($this->price ?? $this->product->base_price);
    }

    public function compareAtPrice(): ?Money
    {
        $amount = $this->compare_at_price ?? $this->product->compare_at_price;

        return $amount ? Money::of($amount) : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Stock
    |--------------------------------------------------------------------------
    */

    public function isInStock(): bool
    {
        return $this->is_active && $this->stock_quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->isInStock()
            && $this->stock_quantity <= (int) config('shop.catalog.low_stock_threshold', 3);
    }

    public function canFulfil(int $quantity): bool
    {
        return $this->is_active && $this->stock_quantity >= $quantity;
    }

    /*
    |--------------------------------------------------------------------------
    | Presentation
    |--------------------------------------------------------------------------
    */

    /**
     * Options keyed by type name, e.g. ['Size' => '50', 'Color' => 'Navy'].
     * This is the shape frozen onto order lines.
     *
     * @return array<string, string>
     */
    public function optionMap(): array
    {
        return $this->optionValues
            ->mapWithKeys(fn (OptionValue $value) => [
                $value->optionType->name => $value->name,
            ])
            ->all();
    }

    /** e.g. "Navy / 50" */
    public function optionSummary(): string
    {
        return $this->optionValues->pluck('name')->join(' / ');
    }
}
