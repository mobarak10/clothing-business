<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'value' => 'integer',
            'minimum_spend' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function scopeUsable(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    /** Why this coupon cannot be used, or null when it can. */
    public function rejectionReason(Money $subtotal): ?string
    {
        if (! $this->is_active) {
            return 'This code is no longer active.';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'This code is not active yet.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This code has expired.';
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return 'This code has reached its usage limit.';
        }

        if ($this->minimum_spend !== null && $subtotal->minor < $this->minimum_spend) {
            $needed = Money::of($this->minimum_spend);

            return "This code requires a minimum spend of {$needed->format()}.";
        }

        return null;
    }

    /**
     * Discount for a subtotal. Never exceeds the subtotal itself -- a coupon
     * reduces an order to zero at most, it does not pay the customer.
     */
    public function discountFor(Money $subtotal): Money
    {
        $discount = $this->type === 'percent'
            ? $subtotal->percentage($this->value)
            : Money::of($this->value);

        return $discount->greaterThan($subtotal) ? $subtotal : $discount;
    }
}
