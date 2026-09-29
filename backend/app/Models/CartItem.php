<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    /** @use HasFactory<\Database\Factories\CartItemFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function unitPrice(): Money
    {
        return Money::of($this->unit_price);
    }

    public function lineTotal(): Money
    {
        return $this->unitPrice()->times($this->quantity);
    }

    /**
     * Whether the price captured when this line was added still matches the
     * catalog. Checkout refuses to proceed on a stale line rather than
     * quietly charging a different amount than the cart displayed.
     */
    public function isPriceStale(): bool
    {
        return $this->variant->price()->minor !== $this->unit_price;
    }
}
