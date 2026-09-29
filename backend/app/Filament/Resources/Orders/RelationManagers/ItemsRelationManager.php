<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * What was bought, frozen at checkout.
 *
 * Read-only by design: these rows are the financial record. Correcting an
 * order means a refund or a replacement order, not editing history.
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->paginated(false)
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->description(fn ($record) => $record->sku)
                    ->wrap(),

                TextColumn::make('options')
                    ->label('Options')
                    ->state(fn ($record) => $record->optionSummary())
                    ->badge(),

                TextColumn::make('unit_price')
                    ->label('Unit price')
                    ->alignEnd()
                    ->formatStateUsing(fn ($record) => $record->unitPrice()->format()),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignEnd(),

                TextColumn::make('line_total')
                    ->label('Total')
                    ->alignEnd()
                    ->weight('medium')
                    ->formatStateUsing(fn ($record) => $record->lineTotal()->format()),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
