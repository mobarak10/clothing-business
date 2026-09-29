<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Exactly one default per type, per customer.
        static::saved(function (Address $address): void {
            if (! $address->is_default) {
                return;
            }

            static::where('user_id', $address->user_id)
                ->where('type', $address->type)
                ->whereKeyNot($address->getKey())
                ->update(['is_default' => false]);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * The frozen shape stored on orders. Keep this stable -- old orders are
     * read back through it.
     *
     * @return array<string, string|null>
     */
    public function toSnapshot(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'phone' => $this->phone,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'district' => $this->district,
            'postcode' => $this->postcode,
            'country_code' => $this->country_code,
        ];
    }
}
