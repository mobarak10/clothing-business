<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Details')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, callable $set, callable $get) {
                                        // Slug is minted once, from name + style
                                        // code. Editing it later would orphan
                                        // every link already pointing at it.
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug($state.' '.$get('sku_root')));
                                        }
                                    }),

                                TextInput::make('sku_root')
                                    ->label('Style code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Shared by every variant of this product.'),

                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->columnSpanFull()
                                    ->prefix('/')
                                    ->suffix('/p')
                                    ->helperText('The storefront URL. Avoid changing after launch.'),

                                TextInput::make('subtitle')
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->placeholder('Two-button, natural shoulder'),

                                Textarea::make('description')
                                    ->rows(5)
                                    ->columnSpanFull(),

                                TextInput::make('composition')
                                    ->placeholder('100% Virgin Wool'),

                                Select::make('fit')
                                    ->options([
                                        'Slim Fit' => 'Slim Fit',
                                        'Regular Fit' => 'Regular Fit',
                                        'Relaxed Fit' => 'Relaxed Fit',
                                    ])
                                    ->native(false),

                                Textarea::make('care_instructions')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Pricing')
                            ->columns(2)
                            ->schema([
                                // Stored in poisha, entered in taka. The two
                                // conversions below are the only place that
                                // translation happens for this form.
                                TextInput::make('base_price')
                                    ->label('Price')
                                    ->required()
                                    ->numeric()
                                    ->prefix(config('shop.currency.symbol'))
                                    ->formatStateUsing(fn (?int $state) => $state === null ? null : $state / 100)
                                    ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100))
                                    ->helperText('Variants inherit this unless they set their own.'),

                                TextInput::make('compare_at_price')
                                    ->label('Compare-at price')
                                    ->numeric()
                                    ->prefix(config('shop.currency.symbol'))
                                    ->formatStateUsing(fn (?int $state) => $state === null ? null : $state / 100)
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? (int) round((float) $state * 100) : null)
                                    ->helperText('Set above the price to show the item as reduced.'),
                            ]),

                        Tab::make('Organisation')
                            ->columns(2)
                            ->schema([
                                Select::make('categories')
                                    ->relationship('categories', 'path')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull()
                                    ->helperText('A product may sit in several categories.'),

                                Toggle::make('is_active')
                                    ->label('Visible on the storefront')
                                    ->default(true),

                                Toggle::make('is_new_arrival')
                                    ->label('Tag as new arrival'),

                                DateTimePicker::make('published_at')
                                    ->label('Publish at')
                                    ->helperText('Leave empty to publish immediately.'),
                            ]),

                        Tab::make('Media')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('gallery')
                                    ->collection('gallery')
                                    ->multiple()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->image()
                                    ->imageEditor()
                                    ->maxFiles(12)
                                    ->columnSpanFull()
                                    ->helperText('First image is used on listing pages. Drag to reorder.'),
                            ]),

                        Tab::make('SEO')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->maxLength(255)
                                    ->placeholder(fn ($get) => $get('name')),

                                Textarea::make('meta_description')
                                    ->rows(3)
                                    ->maxLength(500),
                            ]),
                    ]),
            ]);
    }
}
