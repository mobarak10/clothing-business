<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ContentBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A homepage section. `payload` is passed through as authored -- each block
 * type has its own shape, and the matching Vue component knows how to read it.
 *
 * @mixin ContentBlock
 */
class ContentBlockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'payload' => $this->payload,

            // Blocks that name a product source get them resolved server-side,
            // so the storefront renders in one round trip.
            'products' => ProductCardResource::collection(
                $this->whenNotNull($this->resolvedProducts ?? null)
            ),
        ];
    }
}
