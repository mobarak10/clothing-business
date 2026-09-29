<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\OptionValue;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Size/colour combinations for one product.
 *
 * Stock lives here and is the number staff correct most often, so it is
 * editable inline in the table rather than behind an edit modal.
 */
class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        Select::make('optionValues')
                            ->label('Options')
                            ->relationship('optionValues', 'name')
                            ->multiple()
                            ->preload()
                            ->required()
                            ->columnSpanFull()
                            // Grouping by type keeps Navy and 50 visually apart
                            // in a list that would otherwise be 50 flat entries.
                            ->options(fn () => OptionValue::with('optionType')
                                ->get()
                                ->groupBy(fn (OptionValue $value) => $value->optionType->name)
                                ->map(fn ($values) => $values->pluck('name', 'id'))
                                ->toArray())
                            ->helperText('Pick one value per option type, e.g. Navy and 50.'),

                        TextInput::make('price')
                            ->label('Price override')
                            ->numeric()
                            ->prefix(config('shop.currency.symbol'))
                            ->formatStateUsing(fn (?int $state) => $state === null ? null : $state / 100)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) round((float) $state * 100) : null)
                            ->helperText('Leave empty to inherit the product price.'),

                        TextInput::make('stock_quantity')
                            ->label('Stock')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('weight_grams')
                            ->label('Weight (g)')
                            ->numeric()
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->label('Available')
                            ->default(true),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->defaultSort('position')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('options')
                    ->label('Options')
                    ->state(fn ($record) => $record->optionSummary())
                    ->badge(),

                TextColumn::make('price')
                    ->label('Price')
                    ->alignEnd()
                    ->formatStateUsing(fn ($record) => $record->price()->format())
                    ->description(fn ($record) => $record->price === null ? 'inherited' : 'override'),

                TextInputColumn::make('stock_quantity')
                    ->label('Stock')
                    ->type('number')
                    ->rules(['integer', 'min:0'])
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Available')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Available'),

                TernaryFilter::make('in_stock')
                    ->label('In stock')
                    ->queries(
                        true: fn ($query) => $query->where('stock_quantity', '>', 0),
                        false: fn ($query) => $query->where('stock_quantity', 0),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
