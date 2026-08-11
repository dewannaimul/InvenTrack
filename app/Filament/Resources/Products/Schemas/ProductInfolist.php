<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sku')
                    ->label('SKU'),
                TextEntry::make('barcode')
                    ->placeholder('-'),
                ViewEntry::make('barcode_preview')
                    ->label('')
                    ->view('filament.infolists.product-barcode')
                    ->columnSpanFull(),
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('category.name')
                    ->label('Category')
                    ->placeholder('-'),
                TextEntry::make('brand.name')
                    ->label('Brand')
                    ->placeholder('-'),
                TextEntry::make('unit.name')
                    ->label('Unit')
                    ->placeholder('-'),
                TextEntry::make('cost_price')
                    ->money(),
                TextEntry::make('selling_price')
                    ->money(),
                TextEntry::make('tax_rate')
                    ->numeric(),
                TextEntry::make('reorder_level')
                    ->numeric(),
                ImageEntry::make('image')
                    ->placeholder('-'),
                IconEntry::make('has_variants')
                    ->boolean(),
                IconEntry::make('track_stock')
                    ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Product $record): bool => $record->trashed()),
            ]);
    }
}
