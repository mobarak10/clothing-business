<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Boutiques, nearest first when the visitor shares their location.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'made_to_measure' => ['nullable', 'boolean'],
        ]);

        $query = Store::query()->active();

        if ($request->boolean('made_to_measure')) {
            $query->where('offers_made_to_measure', true);
        }

        if (isset($validated['lat'], $validated['lng'])) {
            $query->nearest((float) $validated['lat'], (float) $validated['lng']);
        } else {
            $query->orderBy('position');
        }

        return StoreResource::collection($query->get());
    }

    public function show(string $slug)
    {
        $store = Store::active()->where('slug', $slug)->firstOrFail();

        return new StoreResource($store);
    }
}
