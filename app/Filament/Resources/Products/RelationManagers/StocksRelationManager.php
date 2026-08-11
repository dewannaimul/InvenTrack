<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StocksRelationManager extends RelationManager
{
    protected static string $relationship = 'stocks';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('warehouse.name')
            ->columns([
                TextColumn::make('warehouse.name')
                    ->label('Warehouse'),
                TextColumn::make('quantity')
                    ->label('On hand')
                    ->numeric(),
                TextColumn::make('reserved_quantity')
                    ->label('Reserved')
                    ->numeric(),
                TextColumn::make('available_quantity')
                    ->label('Available')
                    ->state(fn ($record) => $record->quantity - $record->reserved_quantity)
                    ->numeric(),
            ])
            ->headerActions([
                Action::make('setOpeningStock')
                    ->label('Set opening stock')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('warehouse_id')
                            ->label('Warehouse')
                            ->options(Warehouse::query()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ])
                    ->action(function (array $data) {
                        app(StockService::class)->move(
                            productId: $this->getOwnerRecord()->id,
                            productVariantId: null,
                            warehouseId: (int) $data['warehouse_id'],
                            quantityDelta: (int) $data['quantity'],
                            type: StockMovement::TYPE_ADJUSTMENT,
                            note: 'Opening stock',
                            userId: auth()->id(),
                        );

                        Notification::make()->title('Opening stock recorded')->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('adjust')
                    ->label('Adjust')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        TextInput::make('new_quantity')
                            ->label('New quantity')
                            ->numeric()
                            ->required()
                            ->default(fn ($record) => $record->quantity),
                        Textarea::make('reason')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $delta = (int) $data['new_quantity'] - $record->quantity;

                        if ($delta === 0) {
                            Notification::make()->title('No change in quantity')->warning()->send();

                            return;
                        }

                        app(StockService::class)->move(
                            productId: $record->product_id,
                            productVariantId: $record->product_variant_id,
                            warehouseId: $record->warehouse_id,
                            quantityDelta: $delta,
                            type: StockMovement::TYPE_ADJUSTMENT,
                            note: $data['reason'],
                            userId: auth()->id(),
                        );

                        Notification::make()->title('Stock adjusted')->success()->send();
                    }),
            ]);
    }
}
