<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use App\Models\StockAdjustment;
use App\Services\StockService;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Adjustment info')
                    ->columns(3)
                    ->components([
                        TextInput::make('adjustment_number')
                            ->required()
                            ->default(fn () => 'ADJ-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)))
                            ->unique(StockAdjustment::class, 'adjustment_number', ignoreRecord: true),
                        Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live(),
                        Select::make('reason')
                            ->options([
                                'damaged' => 'Damaged',
                                'lost' => 'Lost',
                                'found' => 'Found',
                                'expired' => 'Expired',
                                'correction' => 'Stock count correction',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
                Section::make('Items')
                    ->components([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $warehouseId = $get('../../warehouse_id');
                                        $onHand = $warehouseId
                                            ? app(StockService::class)->quantityOnHand((int) $state, null, (int) $warehouseId)
                                            : 0;
                                        $set('quantity_before', $onHand);
                                        $set('quantity_after', $onHand);
                                    })
                                    ->columnSpan(2),
                                TextInput::make('quantity_before')
                                    ->label('Current qty')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('quantity_after')
                                    ->label('New qty')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->live(onBlur: true),
                                TextInput::make('difference')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->hidden(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add item')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
