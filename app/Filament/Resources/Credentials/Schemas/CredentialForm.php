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
                                titleAttribute: 'name'
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-o-user')
                            ->helperText('Todos os usuários disponíveis. A validação será feita ao salvar.')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
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
                            ->nullable()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                return $rule->whereNull('deleted_at');
                            })
                            ->prefixIcon('heroicon-o-identification')
                            ->helperText('Código único da credencial (opcional para TCMS em processamento)'),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Documento')
                            ->options(CredentialType::options())
                            ->required()
                            ->native(false)
                            ->live() // Tornar reativo para atualizar o campo de sigilo
                            ->prefixIcon('heroicon-o-document-text')
                            ->helperText('CRED: Credencial de Segurança | TCMS: Termo de Compromisso')
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Ao selecionar TCMS, preencher automaticamente com "Acesso Restrito"
                                if ($state === 'TCMS') {
                                    $set('secrecy', 'AR');
                                } else {
                                    // Limpar o sigilo quando mudar para CRED
                                    $set('secrecy', null);
                                }

                                // Recalcular validade quando tipo mudar
                                $concession = $get('concession');
                                if ($concession) {
                                    $concessionDate = \Carbon\Carbon::parse($concession);

                                    if ($state === 'CRED') {
                                        // CRED: 2 anos
                                        $validity = $concessionDate->copy()->addYears(2);
                                    } elseif ($state === 'TCMS') {
                                        // TCMS: 31/12 do ano da concessão
                                        $validity = $concessionDate->copy()->endOfYear();
                                    } else {
                                        // Limpar validade se tipo não for selecionado
                                        $validity = null;
                                    }

                                    $set('validity', $validity?->format('Y-m-d'));
                                } else {
                                    // Se não há data de concessão, limpar validade
                                    $set('validity', null);
                                }
                            }),

                        Forms\Components\Select::make('secrecy')
                            ->label('Nível de Sigilo')
                            ->options(function ($get) {
                                $type = $get('type');

                                // CRED: apenas R ou S
                                if ($type === 'CRED') {
                                    return [
                                        'R' => 'Reservado',
                                        'S' => 'Secreto',
                                    ];
                                }

                                // TCMS: apenas AR
                                if ($type === 'TCMS') {
                                    return [
                                        'AR' => 'Acesso Restrito',
                                    ];
                                }

                                // Padrão: todas as opções
                                return CredentialSecrecy::options();
                            })
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->helperText(function ($get) {
                                $type = $get('type');
                                if ($type === 'CRED') {
                                    return 'CRED: Reservado ou Secreto';
                                }
                                if ($type === 'TCMS') {
                                    return 'TCMS: Acesso Restrito';
                                }

                                return 'Selecione o tipo de documento primeiro';
                            }),

                        Forms\Components\TextInput::make('credential')
                            ->label('Número da Credencial')
                            ->nullable()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-hashtag')
                            ->helperText('Número ou código identificador da credencial (opcional para TCMS em processamento)'),

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
                            ->live() // Tornar reativo para calcular validade
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Calcular validade automaticamente
                                if ($state) {
                                    $type = $get('type');
                                    $concessionDate = \Carbon\Carbon::parse($state);

                                    if ($type === 'CRED') {
                                        // CRED: 2 anos
                                        $validity = $concessionDate->copy()->addYears(2);
                                    } elseif ($type === 'TCMS') {
                                        // TCMS: 31/12 do ano da concessão
                                        $validity = $concessionDate->copy()->endOfYear();
                                    } else {
                                        $validity = null;
                                    }

                                    $set('validity', $validity?->format('Y-m-d'));
                                } else {
                                    $set('validity', null);
                                }
                            })
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->helperText('Data de concessão. A validade será calculada automaticamente.'),

                        Forms\Components\DatePicker::make('validity')
                            ->label('Data de Validade')
                            ->nullable()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->disabled()
                            ->dehydrated(true) // Permitir salvar o valor calculado
                            ->prefixIcon('heroicon-o-clock')
                            ->helperText('Calculado automaticamente: CRED = 2 anos | TCMS = 31/12 do ano'),
                    ])
                    ->columns(2),
            ]);
    }
}
