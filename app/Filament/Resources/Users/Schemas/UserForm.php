<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Get;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Informações do Usuário")
                    ->schema([
                        TextInput::make("name")
                            ->required()
                            ->maxLength(255)
                            ->label("Nome Completo"),

                        TextInput::make("email")
                            ->label("E-mail")
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make("password")
                            ->label("Senha")
                            ->password()
                            ->required(fn (string $operation): bool => $operation === "create")
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make("Perfis e Permissões")
                    ->schema([
                        Select::make("roles")
                            ->label("Perfis")
                            ->multiple()
                            ->relationship("roles", "name")
                            ->preload()
                            ->searchable()
                            ->helperText("Selecione um ou mais perfis")
                            ->placeholder("Selecione os perfis"),

                        CheckboxList::make("permissions")
                            ->label("Permissões Adicionais")
                            ->relationship("permissions", "name")
                            ->columns(3)
                            ->helperText("Permissões específicas")
                            ->searchable(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }
}
