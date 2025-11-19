<?php

namespace App\Filament\Resources\Credentials;

use App\Filament\Resources\Credentials\Pages\CreateCredential;
use App\Filament\Resources\Credentials\Pages\EditCredential;
use App\Filament\Resources\Credentials\Pages\ListCredentials;
use App\Models\Credential;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class CredentialResource extends Resource
{
    protected static ?string $model = Credential::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informações da Credencial')
                    ->description('Dados principais da credencial de segurança')
                    ->schema([
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
                                'R' => 'Reservado',
                                'S' => 'Secreto',
                            ])
                            ->nullable()
                            ->helperText('Selecione o nível de classificação'),

                        Forms\Components\TextInput::make('credential')
                            ->label('Credencial')
                            ->maxLength(255)
                            ->password()
                            ->revealable()
                            ->helperText('Senha ou código da credencial'),
                    ])
                    ->columns(2),

                \Filament\Schemas\Components\Section::make('Datas')
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
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->after('today')
                            ->helperText('Data de expiração da credencial (deve ser futura)'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fscs')
                    ->label('FSCS')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('FSCS copiado!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\BadgeColumn::make('secrecy')
                    ->label('Sigilo')
                    ->colors([
                        'success' => 'R',
                        'danger' => 'S',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'R' => 'Reservado',
                            'S' => 'Secreto',
                            default => 'N/A'
                        };
                    }),

                Tables\Columns\TextColumn::make('concession')
                    ->label('Concessão')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('validity')
                    ->label('Validade')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        $validity = \Carbon\Carbon::parse($state);
                        $now = now();
                        
                        if ($validity->isPast()) {
                            return 'danger';
                        } elseif ($validity->diffInDays($now) <= 30) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->icon(function ($state) {
                        if (!$state) return null;
                        $validity = \Carbon\Carbon::parse($state);
                        
                        if ($validity->isPast()) {
                            return 'heroicon-o-exclamation-triangle';
                        } elseif ($validity->diffInDays(now()) <= 30) {
                            return 'heroicon-o-clock';
                        }
                        return 'heroicon-o-check-circle';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                
                Tables\Filters\SelectFilter::make('secrecy')
                    ->label('Nível de Sigilo')
                    ->options([
                        'R' => 'Reservado',
                        'S' => 'Secreto',
                    ]),

                Tables\Filters\Filter::make('validity_status')
                    ->label('Status de Validade')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'valid' => 'Válidas',
                                'expiring' => 'Expirando em 30 dias',
                                'expired' => 'Expiradas',
                            ])
                            ->placeholder('Todos os status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['status'] === 'valid',
                                fn (Builder $query, $date): Builder => $query->where('validity', '>', now()->addDays(30)),
                            )
                            ->when(
                                $data['status'] === 'expiring',
                                fn (Builder $query, $date): Builder => $query->whereBetween('validity', [now(), now()->addDays(30)]),
                            )
                            ->when(
                                $data['status'] === 'expired',
                                fn (Builder $query, $date): Builder => $query->where('validity', '<', now()),
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Deletar Selecionados')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCredentials::route('/'),
            'create' => CreateCredential::route('/create'),
            'edit' => EditCredential::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
