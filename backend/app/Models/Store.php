<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    /** @use HasFactory<\Database\Factories\StoreFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'offers_made_to_measure' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Orders stores by distance from a point, nearest first.
     *
     * Uses the haversine formula in SQL. For a boutique network of tens of
     * stores this is far simpler than a spatial index and just as fast.
     */
    public function scopeNearest(Builder $query, float $latitude, float $longitude): Builder
    {
        $haversine = '(6371 * acos(
            cos(radians(?)) * cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) +
            sin(radians(?)) * sin(radians(latitude))
        ))';

        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw("stores.*, {$haversine} AS distance_km", [$latitude, $longitude, $latitude])
            ->orderBy('distance_km');
    }

    public function singleLineAddress(): string
    {
        return collect([$this->line1, $this->line2, $this->city, $this->postcode])
            ->filter()
            ->join(', ');
    }
}
