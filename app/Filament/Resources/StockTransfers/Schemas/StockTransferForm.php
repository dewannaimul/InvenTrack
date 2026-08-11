<?php

namespace App\Filament\Resources\StockTransfers\Schemas;

use App\Models\StockTransfer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class StockTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transfer info')
                    ->columns(3)
                    ->components([
                        TextInput::make('transfer_number')
                            ->required()
                            ->default(fn () => 'TR-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)))
                            ->unique(StockTransfer::class, 'transfer_number', ignoreRecord: true),
                        Select::make('from_warehouse_id')
                            ->label('From warehouse')
                            ->relationship('fromWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->different('to_warehouse_id'),
                        Select::make('to_warehouse_id')
                            ->label('To warehouse')
                            ->relationship('toWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('transfer_date')
                            ->required()
                            ->default(now()),
                        Select::make('status')
                            ->options([
                                StockTransfer::STATUS_PENDING => 'Pending',
                                StockTransfer::STATUS_IN_TRANSIT => 'In transit',
                                StockTransfer::STATUS_COMPLETED => 'Completed',
                                StockTransfer::STATUS_CANCELLED => 'Cancelled',
                            ])
                            ->default(StockTransfer::STATUS_PENDING)
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
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Add item')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
