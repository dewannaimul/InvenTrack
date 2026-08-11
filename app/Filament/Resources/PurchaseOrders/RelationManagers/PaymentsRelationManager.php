<?php

namespace App\Filament\Resources\PurchaseOrders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
                Select::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank transfer',
                        'card' => 'Card',
                        'cheque' => 'Cheque',
                    ])
                    ->default('cash')
                    ->required(),
                DatePicker::make('paid_on')
                    ->required()
                    ->default(now()),
                TextInput::make('reference_number'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference_number')
            ->columns([
                TextColumn::make('paid_on')->date(),
                TextColumn::make('amount')->money('usd'),
                TextColumn::make('method')->badge(),
                TextColumn::make('reference_number'),
                TextColumn::make('createdBy.name')->label('Recorded by'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = auth()->id();

                        return $data;
                    })
                    ->after(fn () => $this->recalculateBalance()),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->after(fn () => $this->recalculateBalance()),
            ]);
    }

    protected function recalculateBalance(): void
    {
        $order = $this->getOwnerRecord();
        $paid = (float) $order->payments()->sum('amount');

        $order->update([
            'amount_paid' => $paid,
            'payment_status' => match (true) {
                $paid <= 0 => 'unpaid',
                $paid >= (float) $order->total => 'paid',
                default => 'partial',
            },
        ]);
    }
}
