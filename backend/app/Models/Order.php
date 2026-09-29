<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'shipping_total' => 'integer',
            'tax_total' => 'integer',
            'grand_total' => 'integer',
            'placed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** The attempt that actually settled, if any. */
    public function successfulPayment(): ?Payment
    {
        return $this->payments()
            ->where('status', PaymentStatus::Paid)
            ->latest('paid_at')
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePlaced(Builder $query): Builder
    {
        return $query->whereNotNull('placed_at');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /*
    |--------------------------------------------------------------------------
    | Money
    |--------------------------------------------------------------------------
    */

    public function subtotal(): Money
    {
        return Money::of($this->subtotal, $this->currency);
    }

    public function discountTotal(): Money
    {
        return Money::of($this->discount_total, $this->currency);
    }

    public function shippingTotal(): Money
    {
        return Money::of($this->shipping_total, $this->currency);
    }

    public function grandTotal(): Money
    {
        return Money::of($this->grand_total, $this->currency);
    }

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return $this->payment_status->isSettled();
    }

    public function canBeCancelled(): bool
    {
        return ! $this->status->isFinal()
            && in_array($this->status, [OrderStatus::Pending, OrderStatus::Processing], true);
    }

    /**
     * Generates the next human-facing reference, e.g. ATL-2026-000148.
     *
     * Sequence is per calendar year. Call inside the same transaction that
     * writes the order so two concurrent checkouts cannot claim one number.
     */
    public static function nextOrderNumber(): string
    {
        $year = now()->year;
        $prefix = strtoupper(substr((string) config('shop.brand.name', 'ATL'), 0, 3));

        $latest = static::withoutGlobalScopes()
            ->where('order_number', 'like', "{$prefix}-{$year}-%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('order_number');

        $sequence = $latest ? ((int) substr($latest, -6)) + 1 : 1;

        return sprintf('%s-%d-%06d', $prefix, $year, $sequence);
    }
}
