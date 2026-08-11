<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->helperText('e.g. Pieces, Kilogram, Box'),
                TextInput::make('symbol')
                    ->required()
                    ->maxLength(10)
                    ->helperText('e.g. pcs, kg, box'),
            ]);
    }
}
