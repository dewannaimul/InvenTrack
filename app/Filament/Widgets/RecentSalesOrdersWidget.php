<?php

namespace App\Filament\Widgets;

use App\Models\SalesOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSalesOrdersWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent sales orders';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SalesOrder::query()->latest('order_date'))
            ->columns([
                TextColumn::make('order_number')->label('Order #'),
                TextColumn::make('customer.name'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        SalesOrder::STATUS_DRAFT => 'gray',
                        SalesOrder::STATUS_CONFIRMED => 'info',
                        SalesOrder::STATUS_SHIPPED => 'warning',
                        SalesOrder::STATUS_COMPLETED => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('total')->money('usd'),
                TextColumn::make('order_date')->date(),
            ])
            ->paginated([5, 10, 25])
            ->defaultSort('order_date', 'desc');
    }
}
