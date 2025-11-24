<?php

namespace App\Filament\Resources\Credentials\Schemas;

use App\Enums\CredentialSecrecy;
use App\Enums\CredentialType;
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
                    ->icon('heroicon-o-shield-check')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuário Responsável')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query, $livewire) {
                                    // Pegar o ID do registro atual (se estiver editando)
                                    $recordId = $livewire->record?->id ?? null;

                                    // Filtrar usuários que NÃO têm credenciais ativas
                                    $query->whereDoesntHave('credentials', function ($credentialQuery) use ($recordId) {
                                        $credentialQuery->whereNull('deleted_at');
                                        
                                        // Se estiver editando, ignorar a credencial atual
                                        if ($recordId) {
                                            $credentialQuery->where('id', '!=', $recordId);
                                        }
                                    });
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-o-user')
                            ->helperText('Apenas usuários sem credenciais ativas são exibidos')
                            ->rules([
                                function ($livewire) {
                                    return function (string $attribute, $value, \Closure $fail) use ($livewire) {
                                        // Ignorar validação se estamos editando o mesmo registro
                                        $recordId = $livewire->record?->id ?? null;

                                        $query = \App\Models\Credential::where('user_id', $value)
                                            ->whereNull('deleted_at');

                                        if ($recordId) {
                                            $query->where('id', '!=', $recordId);
                                        }

                                        if ($query->exists()) {
                                            $fail('Este usuário já possui uma credencial ativa. Apenas uma credencial por usuário é permitida.');
                                        }
                                    };
                                },
                            ]),

                        Forms\Components\TextInput::make('fscs')
                            ->label('FSCS')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                return $rule->whereNull('deleted_at');
                            })
                            ->prefixIcon('heroicon-o-identification')
                            ->helperText('Código único da credencial'),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Documento')
                            ->options(CredentialType::options())
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-document-text')
                            ->helperText('CRED: Credencial de Segurança | TCMS: Termo de Compromisso'),

                        Forms\Components\Select::make('secrecy')
                            ->label('Nível de Sigilo')
                            ->options(CredentialSecrecy::options())
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->helperText('R: Reservado | S: Secreto'),

                        Forms\Components\TextInput::make('credential')
                            ->label('Número da Credencial')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-hashtag')
                            ->helperText('Número ou código identificador da credencial (texto simples)'),

                        Forms\Components\Textarea::make('observation')
                            ->label('Observações')
                            ->nullable()
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Observações adicionais sobre a credencial'),
                    ])
                    ->columns(2),

                Section::make('Datas')
                    ->description('Controle de validade e concessão')
                    ->icon('heroicon-o-calendar')
                    ->collapsible()
                    ->schema([
                        Forms\Components\DatePicker::make('concession')
                            ->label('Data de Concessão')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->helperText('Data de concessão. A validade será calculada automaticamente.'),

                        Forms\Components\DatePicker::make('validity')
                            ->label('Data de Validade')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefixIcon('heroicon-o-clock')
                            ->helperText('Calculado automaticamente: CRED = 2 anos | TCMS = 31/12 do ano'),
                    ])
                    ->columns(2),
            ]);
    }
}
