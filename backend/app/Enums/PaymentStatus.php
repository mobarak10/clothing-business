<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';

    /** Gateway session opened; shopper is on the gateway's page. */
    case Initiated = 'initiated';

    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /** Cash on delivery: money changes hands at the door, not online. */
    case DueOnDelivery = 'due_on_delivery';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Initiated => 'Awaiting payment',
            self::Paid => 'Paid',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
            self::DueOnDelivery => 'Due on delivery',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Pending, self::Initiated, self::DueOnDelivery => 'warning',
            self::Failed, self::Cancelled, self::Refunded => 'danger',
        };
    }

    public function isSettled(): bool
    {
        return in_array($this, [self::Paid, self::DueOnDelivery], true);
    }
}
