<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'unit_price' => 'integer',
            'quantity' => 'integer',
            'line_total' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** May be null if the variant has since been removed from the catalog. */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function unitPrice(): Money
    {
        return Money::of($this->unit_price, $this->order->currency ?? null);
    }

    public function lineTotal(): Money
    {
        return Money::of($this->line_total, $this->order->currency ?? null);
    }

    /** e.g. "Navy / 50" */
    public function optionSummary(): string
    {
        return collect($this->options ?? [])->values()->join(' / ');
    }
}
