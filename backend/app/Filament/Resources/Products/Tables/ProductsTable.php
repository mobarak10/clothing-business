<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->label('')
                    ->collection('gallery')
                    ->conversion('thumb')
                    ->limit(1)
                    ->height(56),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->sku_root)
                    ->wrap(),

                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->limitList(2)
                    ->toggleable(),

                TextColumn::make('base_price')
                    ->label('Price')
                    ->alignEnd()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->price()->format())
                    // Struck-through original when the item is reduced.
                    ->description(fn ($record) => $record->isOnSale()
                        ? 'was '.$record->compareAtPrice()->format()
                        : null),

                TextColumn::make('variants_count')
                    ->label('Variants')
                    ->counts('variants')
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->alignEnd()
                    ->state(fn ($record) => $record->totalStock())
                    ->color(fn ($state) => match (true) {
                        $state === 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    })
                    ->badge(),

                IconColumn::make('is_active')
                    ->label('Live')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_new_arrival')
                    ->label('New')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Category')
                    ->options(fn () => Category::orderBy('path')->pluck('path', 'id'))
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->whereHas('categories', fn (Builder $q) => $q->whereKey($data['value']))
                        : $query),

                TernaryFilter::make('is_active')->label('Visible'),
                TernaryFilter::make('is_new_arrival')->label('New arrival'),

                TernaryFilter::make('in_stock')
                    ->label('In stock')
                    ->queries(
                        true: fn (Builder $query) => $query->inStock(),
                        false: fn (Builder $query) => $query->whereDoesntHave(
                            'variants',
                            fn (Builder $q) => $q->where('stock_quantity', '>', 0)
                        ),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
