<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product details')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->required()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->default(fn () => 'SKU-'.strtoupper(Str::random(8))),
                        TextInput::make('barcode')
                            ->unique(Product::class, 'barcode', ignoreRecord: true),
                        FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
                Section::make('Classification')
                    ->columns(3)
                    ->components([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')->required(),
                            ]),
                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')->required(),
                            ]),
                        Select::make('unit_id')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('symbol')->required(),
                            ]),
                    ]),
                Section::make('Pricing & stock')
                    ->columns(4)
                    ->components([
                        TextInput::make('cost_price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        TextInput::make('selling_price')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        TextInput::make('tax_rate')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),
                        TextInput::make('reorder_level')
                            ->label('Reorder level')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Low-stock alert threshold'),
                    ]),
                Section::make('Options')
                    ->columns(3)
                    ->components([
                        Toggle::make('has_variants')
                            ->label('Has variants')
                            ->live()
                            ->helperText('Enable if this product comes in different sizes/colors'),
                        Toggle::make('track_stock')
                            ->label('Track stock')
                            ->default(true),
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }
}
