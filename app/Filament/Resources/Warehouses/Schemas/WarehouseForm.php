<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use App\Models\Warehouse;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required()
                    ->unique(Warehouse::class, 'code', ignoreRecord: true),
                TextInput::make('phone')
                    ->tel(),
                Textarea::make('address')
                    ->columnSpanFull(),
                Toggle::make('is_default')
                    ->helperText('Used as the default warehouse for new orders'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
