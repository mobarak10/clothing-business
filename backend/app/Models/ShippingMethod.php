<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    /** @use HasFactory<\Database\Factories\ShippingMethodFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'integer',
            'free_over' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('position');
    }

    /** Cost for a given order subtotal, honouring the free-shipping threshold. */
    public function costFor(Money $subtotal): Money
    {
        if ($this->free_over !== null && $subtotal->minor >= $this->free_over) {
            return Money::zero();
        }

        return Money::of($this->price);
    }

    public function deliveryWindow(): ?string
    {
        if (! $this->estimated_days_min) {
            return null;
        }

        return $this->estimated_days_max && $this->estimated_days_max !== $this->estimated_days_min
            ? "{$this->estimated_days_min}-{$this->estimated_days_max} business days"
            : "{$this->estimated_days_min} business days";
    }
}
