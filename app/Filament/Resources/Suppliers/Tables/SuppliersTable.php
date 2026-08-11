<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Filament\Actions\ActivationBulkActions;
use App\Filament\Actions\ExportCsvBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('purchase_orders_count')
                    ->counts('purchaseOrders')
                    ->label('Purchase orders'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportCsvBulkAction::make('suppliers', [
                        'Name' => 'name',
                        'Company' => 'company',
                        'Email' => 'email',
                        'Phone' => 'phone',
                        'Tax number' => 'tax_number',
                        'Active' => fn ($r) => $r->is_active ? 'Yes' : 'No',
                    ]),
                    ...ActivationBulkActions::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
