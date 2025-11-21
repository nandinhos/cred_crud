<?php

namespace App\Filament\Resources\Credentials\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CredentialForm
{
    /**
     * Configurar o formulário de credenciais
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Credencial')
                    ->description('Dados principais da credencial de segurança')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuário Responsável')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Usuário responsável por esta credencial'),

                        Forms\Components\TextInput::make('fscs')
                            ->label('FSCS')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                return $rule->whereNull('deleted_at');
                            })
                            ->helperText('Código único da credencial'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Nome descritivo da credencial'),

                        Forms\Components\Select::make('secrecy')
                            ->label('Nível de Sigilo')
                            ->options([
                                'O' => 'Ostensivo',
                                'R' => 'Reservado',
                                'S' => 'Secreto',
                            ])
                            ->required()
                            ->default('O')
                            ->helperText('Selecione o nível de classificação'),

                        Forms\Components\TextInput::make('credential')
                            ->label('Número da Credencial')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Número ou código identificador da credencial (texto simples)'),
                    ])
                    ->columns(2),

                Section::make('Datas')
                    ->description('Controle de validade e concessão')
                    ->schema([
                        Forms\Components\DatePicker::make('concession')
                            ->label('Data de Concessão')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Data em que a credencial foi concedida'),

                        Forms\Components\DatePicker::make('validity')
                            ->label('Data de Validade')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->helperText('Data de expiração da credencial (opcional)'),
                    ])
                    ->columns(2),
            ]);
    }
}
