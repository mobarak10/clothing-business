<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cart $cart): void {
            $cart->token ??= (string) Str::uuid();
            $cart->currency ??= config('shop.currency.code');
            $cart->expires_at ??= now()->addDays(
                (int) config('shop.cart.guest_lifetime_days', 30)
            );
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    |
    | Line-level only. Shipping, discounts and the grand total are decided at
    | checkout, where the live prices and stock are re-checked.
    |
    */

    public function subtotal(): Money
    {
        return $this->items->reduce(
            fn (Money $carry, CartItem $item) => $carry->plus($item->lineTotal()),
            Money::zero($this->currency),
        );
    }

    public function itemCount(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Fold another cart's lines into this one.
     *
     * Runs when a guest logs in. Quantities add up rather than overwrite, and
     * the surviving line keeps the current price -- the shopper is about to see
     * the cart again anyway, so the newer figure is the honest one.
     */
    public function mergeFrom(self $other): void
    {
        foreach ($other->items as $incoming) {
            $existing = $this->items()
                ->where('product_variant_id', $incoming->product_variant_id)
                ->first();

            if ($existing) {
                $max = (int) config('shop.cart.max_quantity_per_line', 10);

                $existing->update([
                    'quantity' => min($existing->quantity + $incoming->quantity, $max),
                    'unit_price' => $incoming->unit_price,
                ]);

                continue;
            }

            $this->items()->create([
                'product_variant_id' => $incoming->product_variant_id,
                'quantity' => $incoming->quantity,
                'unit_price' => $incoming->unit_price,
            ]);
        }

        $this->load('items');
    }
}
