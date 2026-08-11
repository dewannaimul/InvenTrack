<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Actions\ActivationBulkActions;
use App\Filament\Actions\ExportCsvBulkAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withSum('stocks as stock_quantity', 'quantity'))
            ->columns([
                ImageColumn::make('image')
                    ->circular(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('selling_price')
                    ->money('usd')
                    ->sortable(),
                TextColumn::make('cost_price')
                    ->money('usd')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock_quantity')
                    ->label('In stock')
                    ->state(fn ($record) => (int) ($record->stock_quantity ?? 0))
                    ->badge()
                    ->color(fn ($record) => (int) ($record->stock_quantity ?? 0) <= $record->reorder_level ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('reorder_level')
                    ->label('Reorder at')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_variants')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Category'),
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Brand'),
                TernaryFilter::make('is_active'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('printLabels')
                        ->label('Print labels')
                        ->icon('heroicon-o-qr-code')
                        ->color('gray')
                        ->schema([
                            TextInput::make('quantity')
                                ->label('Copies per product')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $ids = $records->pluck('id')->implode(',');

                            return redirect()->route('documents.product-labels', [
                                'ids' => $ids,
                                'qty' => $data['quantity'],
                            ]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    ExportCsvBulkAction::make('products', [
                        'SKU' => 'sku',
                        'Barcode' => 'barcode',
                        'Name' => 'name',
                        'Category' => 'category.name',
                        'Brand' => 'brand.name',
                        'Cost price' => 'cost_price',
                        'Selling price' => 'selling_price',
                        'Reorder level' => 'reorder_level',
                        'Active' => fn ($r) => $r->is_active ? 'Yes' : 'No',
                    ]),
                    ...ActivationBulkActions::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
