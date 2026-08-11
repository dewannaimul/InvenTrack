<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->required()
                    ->default(fn () => 'VAR-'.strtoupper(Str::random(8))),
                TextInput::make('barcode'),
                KeyValue::make('variant_attributes')
                    ->label('Attributes')
                    ->keyLabel('Attribute')
                    ->valueLabel('Value')
                    ->helperText('e.g. Color = Red, Size = L')
                    ->columnSpanFull(),
                TextInput::make('cost_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('selling_price')
                    ->numeric()
                    ->prefix('$'),
                FileUpload::make('image')
                    ->image()
                    ->directory('product-variants'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->searchable(),
                TextColumn::make('variant_attributes')
                    ->label('Attributes')
                    ->formatStateUsing(fn ($state) => collect($state ?? [])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ')),
                TextColumn::make('selling_price')
                    ->money('usd'),
                TextColumn::make('stocks_sum_quantity')
                    ->sum('stocks', 'quantity')
                    ->label('In stock'),
                IconColumn::make('is_active')
                    ->boolean(),
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
