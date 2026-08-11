<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\Product;
use App\Models\PurchaseOrder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order info')
                    ->columns(3)
                    ->components([
                        TextInput::make('po_number')
                            ->required()
                            ->default(fn () => 'PO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)))
                            ->unique(PurchaseOrder::class, 'po_number', ignoreRecord: true),
                        Select::make('supplier_id')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('warehouse_id')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('order_date')
                            ->required()
                            ->default(now()),
                        DatePicker::make('expected_date'),
                        Select::make('status')
                            ->options([
                                PurchaseOrder::STATUS_DRAFT => 'Draft',
                                PurchaseOrder::STATUS_ORDERED => 'Ordered',
                                PurchaseOrder::STATUS_PARTIALLY_RECEIVED => 'Partially received',
                                PurchaseOrder::STATUS_RECEIVED => 'Received',
                                PurchaseOrder::STATUS_CANCELLED => 'Cancelled',
                            ])
                            ->default(PurchaseOrder::STATUS_DRAFT)
                            ->disabledOn('edit')
                            ->dehydrated()
                            ->required(),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
                Section::make('Items')
                    ->components([
                        Repeater::make('items')
                            ->relationship()
                            ->live()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $product = Product::find($state);
                                        $set('unit_cost', $product?->cost_price ?? 0);
                                    })
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(onBlur: true),
                                TextInput::make('unit_cost')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->prefix('$')
                                    ->live(onBlur: true),
                                Placeholder::make('line_total')
                                    ->label('Line total')
                                    ->content(fn ($get) => '$'.number_format(((float) $get('quantity')) * ((float) $get('unit_cost')), 2)),
                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->default(0)
                                    ->dehydrated()
                                    ->hidden(),
                            ])
                            ->columns(5)
                            ->defaultItems(1)
                            ->addActionLabel('Add item')
                            ->columnSpanFull(),
                    ]),
                Section::make('Totals')
                    ->columns(4)
                    ->components([
                        TextInput::make('tax_total')
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        TextInput::make('discount_total')
                            ->numeric()
                            ->default(0)
                            ->prefix('$'),
                        TextInput::make('subtotal')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                        TextInput::make('total')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
