<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\Money;

/**
 * The one shape every price takes across the API.
 *
 * Both halves are deliberate: `amount` is the integer the storefront does
 * arithmetic on, `formatted` is what it prints. Letting the client format
 * money would mean re-implementing currency rules in JavaScript.
 */
final class MoneyResource
{
    public static function make(?Money $money): ?array
    {
        if (! $money) {
            return null;
        }

        return [
            'amount' => $money->minor,
            'formatted' => $money->format(),
            'currency' => $money->currency,
        ];
    }
}
