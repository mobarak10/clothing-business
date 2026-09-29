<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Store
 */
class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,

            'address' => [
                'line1' => $this->line1,
                'line2' => $this->line2,
                'city' => $this->city,
                'district' => $this->district,
                'postcode' => $this->postcode,
                'country_code' => $this->country_code,
                'single_line' => $this->singleLineAddress(),
            ],

            'phone' => $this->phone,
            'email' => $this->email,

            'coordinates' => $this->latitude && $this->longitude
                ? ['lat' => (float) $this->latitude, 'lng' => (float) $this->longitude]
                : null,

            'opening_hours' => $this->opening_hours,
            'offers_made_to_measure' => (bool) $this->offers_made_to_measure,
            'image' => $this->image_path ? asset("storage/{$this->image_path}") : null,

            // Present only when the request supplied coordinates to sort by.
            'distance_km' => $this->when(
                isset($this->distance_km),
                fn () => round((float) $this->distance_km, 1),
            ),
        ];
    }
}
