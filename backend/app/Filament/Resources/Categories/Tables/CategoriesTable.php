<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // Sorting by path renders the tree in reading order: a parent is
            // always immediately followed by its children.
            ->defaultSort('path')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    // Indent by depth so the hierarchy is legible at a glance.
                    ->formatStateUsing(fn ($state, $record) => str_repeat('— ', $record->depth()).$state),

                TextColumn::make('path')
                    ->label('URL')
                    ->prefix('/')
                    ->color('gray')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('position')
                    ->label('Order')
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Live')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Visible'),
                TernaryFilter::make('is_featured')->label('Featured'),
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
