<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set) {
                                // Only auto-slug on create: changing a slug later
                                // breaks live URLs and any links pointing at them.
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The URL segment. Changing this breaks existing links.'),

                        Select::make('parent_id')
                            ->label('Parent category')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'path',
                                // A category cannot be its own parent, nor sit
                                // beneath one of its own descendants -- that
                                // would create a cycle the path builder cannot
                                // resolve.
                                modifyQueryUsing: function (Builder $query, ?Category $record) {
                                    if ($record) {
                                        $query->whereNotIn('id', $record->descendantAndSelfIds());
                                    }

                                    return $query->orderBy('path');
                                },
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('None (top level)'),

                        TextInput::make('position')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first.'),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Presentation')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Category image')
                            ->image()
                            ->imageEditor()
                            ->directory('categories')
                            ->disk('public')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Visible on the storefront')
                            ->default(true),

                        Toggle::make('is_featured')
                            ->label('Feature in the mega-menu')
                            ->helperText('Promotes this category into the menu\'s highlighted column.'),
                    ]),

                Section::make('Search engines')
                    ->collapsed()
                    ->columns(1)
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(255)
                            ->placeholder(fn ($get) => $get('name')),

                        Textarea::make('meta_description')
                            ->rows(2)
                            ->maxLength(500),
                    ]),
            ]);
    }
}
