<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * An amount of money, held as integer minor units (poisha).
 *
 * Money never touches a float in this application. 0.1 + 0.2 is not 0.3 in
 * binary floating point, and an order total that is off by a poisha is a
 * reconciliation problem later. Every price column stores minor units and
 * every calculation goes through this class.
 */
final readonly class Money
{
    private function __construct(
        public int $minor,
        public string $currency,
    ) {}

    public static function of(int $minor, ?string $currency = null): self
    {
        return new self($minor, $currency ?? config('shop.currency.code'));
    }

    public static function zero(?string $currency = null): self
    {
        return self::of(0, $currency);
    }

    /**
     * Build from a major-unit figure such as 1250.50 taka.
     * Only for parsing operator input -- never for arithmetic.
     */
    public static function fromMajor(float|int|string $amount, ?string $currency = null): self
    {
        $divisor = (int) config('shop.currency.divisor', 100);

        return self::of((int) round((float) $amount * $divisor), $currency);
    }

    public function plus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minor + $other->minor, $this->currency);
    }

    public function minus(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->minor - $other->minor, $this->currency);
    }

    public function times(int $multiplier): self
    {
        return new self($this->minor * $multiplier, $this->currency);
    }

    /**
     * Apply a percentage given in basis points (1000 = 10.00%).
     *
     * Rounds half up, matching how the storefront displays the discount.
     */
    public function percentage(int $basisPoints): self
    {
        return new self((int) round($this->minor * $basisPoints / 10000), $this->currency);
    }

    /** Clamps at zero -- a discount must never push a total negative. */
    public function atLeastZero(): self
    {
        return $this->minor < 0 ? new self(0, $this->currency) : $this;
    }

    public function isZero(): bool
    {
        return $this->minor === 0;
    }

    public function greaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->minor > $other->minor;
    }

    public function greaterThanOrEqual(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->minor >= $other->minor;
    }

    /** Major units, for display only. */
    public function toMajor(): float
    {
        return $this->minor / (int) config('shop.currency.divisor', 100);
    }

    /** e.g. "৳1,250" */
    public function format(): string
    {
        $symbol = config('shop.currency.symbol', '৳');
        $major = $this->toMajor();

        // Whole amounts read better without trailing zeros on a fashion site.
        $decimals = fmod($major, 1.0) === 0.0 ? 0 : 2;

        return $symbol.number_format($major, $decimals);
    }

    public function __toString(): string
    {
        return $this->format();
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                "Cannot combine {$this->currency} with {$other->currency}."
            );
        }
    }
}
