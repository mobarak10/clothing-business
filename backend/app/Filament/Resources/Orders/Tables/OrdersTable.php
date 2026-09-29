<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->copyable()
                    ->weight('medium'),

                TextColumn::make('customer')
                    ->label('Customer')
                    ->state(fn ($record) => trim(
                        ($record->shipping_address['first_name'] ?? '').' '.
                        ($record->shipping_address['last_name'] ?? '')
                    ) ?: $record->email)
                    ->description(fn ($record) => $record->email)
                    ->searchable(query: fn (Builder $query, string $search) => $query
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items')
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->grandTotal()->format())
                    ->weight('medium'),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('placed_at')
                    ->label('Placed')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class)
                    ->multiple(),

                SelectFilter::make('payment_status')
                    ->label('Payment')
                    ->options(PaymentStatus::class)
                    ->multiple(),

                // The working queue: paid but not yet dispatched.
                Filter::make('awaiting_fulfilment')
                    ->label('Awaiting fulfilment')
                    ->query(fn (Builder $query) => $query
                        ->whereIn('status', [OrderStatus::Pending, OrderStatus::Processing])
                        ->where('payment_status', PaymentStatus::Paid)),
            ])
            ->recordActions([
                EditAction::make()->label('Open'),
            ]);
    }
}
