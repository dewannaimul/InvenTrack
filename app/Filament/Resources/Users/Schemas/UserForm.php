<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true),
                        TextInput::make('phone')
                            ->tel(),
                        FileUpload::make('avatar')
                            ->image()
                            ->avatar()
                            ->directory('avatars'),
                        TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->revealable(),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }
}
