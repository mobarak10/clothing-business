<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Orders are created by checkout, never by staff. This form therefore edits
 * only what an operator legitimately changes after the fact -- fulfilment
 * status and internal notes -- and shows everything else read-only.
 */
class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Fulfilment')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Order status')
                            ->options(OrderStatus::class)
                            ->native(false)
                            ->required(),

                        Select::make('payment_status')
                            ->label('Payment status')
                            ->options(PaymentStatus::class)
                            ->native(false)
                            ->required()
                            ->helperText('Only correct this by hand when reconciling with the gateway.'),

                        Textarea::make('staff_note')
                            ->label('Internal note')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Never shown to the customer.'),
                    ]),

                Section::make('Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_number')->label('Order number'),
                        TextEntry::make('placed_at')->label('Placed')->dateTime('d M Y, H:i'),
                        TextEntry::make('shipping_method_name')->label('Shipping method'),

                        TextEntry::make('subtotal')
                            ->label('Subtotal')
                            ->state(fn ($record) => $record->subtotal()->format()),

                        TextEntry::make('discount_total')
                            ->label('Discount')
                            ->state(fn ($record) => $record->discountTotal()->format())
                            ->description(fn ($record) => $record->coupon_code),

                        TextEntry::make('shipping_total')
                            ->label('Shipping')
                            ->state(fn ($record) => $record->shippingTotal()->format()),

                        TextEntry::make('grand_total')
                            ->label('Grand total')
                            ->state(fn ($record) => $record->grandTotal()->format())
                            ->weight('bold')
                            ->columnSpanFull(),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('email')->label('Email')->copyable(),
                        TextEntry::make('phone')->label('Phone')->copyable(),

                        TextEntry::make('customer_note')
                            ->label('Customer note')
                            ->columnSpanFull()
                            ->placeholder('None'),
                    ]),

                Grid::make(2)->schema([
                    Section::make('Shipping address')->schema([
                        TextEntry::make('shipping_address')
                            ->hiddenLabel()
                            ->state(fn ($record) => self::formatAddress($record->shipping_address)),
                    ]),

                    Section::make('Billing address')->schema([
                        TextEntry::make('billing_address')
                            ->hiddenLabel()
                            ->state(fn ($record) => self::formatAddress($record->billing_address)),
                    ]),
                ]),
            ]);
    }

    private static function formatAddress(?array $address): string
    {
        if (! $address) {
            return '—';
        }

        return collect([
            trim(($address['first_name'] ?? '').' '.($address['last_name'] ?? '')),
            $address['company'] ?? null,
            $address['line1'] ?? null,
            $address['line2'] ?? null,
            collect([$address['district'] ?? null, $address['city'] ?? null, $address['postcode'] ?? null])
                ->filter()->join(', '),
            $address['country_code'] ?? null,
            $address['phone'] ?? null,
        ])->filter()->join("\n");
    }
}
