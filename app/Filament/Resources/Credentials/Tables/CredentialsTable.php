<?php

namespace App\Filament\Resources\Credentials\Tables;

use App\Enums\BadgeColor;
use App\Filament\Resources\Credentials\Pages;
use App\Models\Credential;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CredentialsTable
{
    /**
     * Configurar a tabela de credenciais
     */
    public static function configure(Table $table): Table
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

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state): string => BadgeColor::forType($state->value ?? $state))
                    ->formatStateUsing(function ($state) {
                        return is_object($state) ? $state->value : $state;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record): string => $record->status_color)
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Ordenação customizada por status
                        return $query->orderBy('validity', $direction);
                    }),

                Tables\Columns\TextColumn::make('secrecy')
                    ->label('Sigilo')
                    ->badge()
                    ->color(fn ($state): string => BadgeColor::forSecrecy($state->value ?? $state))
                    ->formatStateUsing(function ($state) {
                        if (is_object($state) && method_exists($state, 'label')) {
                            return $state->label();
                        }

                        return match ($state) {
                            'R' => 'Reservado',
                            'S' => 'Secreto',
                            default => 'N/A'
                        };
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('credential')
                    ->label('Número da Credencial')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Número copiado!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('concession')
                    ->label('Concessão')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('validity')
                    ->label('Validade')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => BadgeColor::forValidity($state ? \Carbon\Carbon::parse($state) : null))
                    ->icon(function ($state) {
                        if (! $state) {
                            return null;
                        }
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
                TrashedFilter::make(),

                SelectFilter::make('type')
                    ->label('Tipo de Documento')
                    ->options([
                        'CRED' => 'CRED - Credencial de Segurança',
                        'TCMS' => 'TCMS - Termo de Compromisso',
                    ]),

                SelectFilter::make('secrecy')
                    ->label('Nível de Sigilo')
                    ->options([
                        'R' => 'Reservado',
                        'S' => 'Secreto',
                    ]),

                Filter::make('status')
                    ->label('Status da Credencial')
                    ->form([
                        Forms\Components\Select::make('status_filter')
                            ->label('Status')
                            ->options([
                                'negada' => 'Negada',
                                'vencida' => 'Vencida',
                                'em_processamento' => 'Em Processamento',
                                'pendente' => 'Pendente',
                                'ativa' => 'Ativa',
                            ])
                            ->placeholder('Todos os status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['status_filter'])) {
                            return $query;
                        }

                        return match ($data['status_filter']) {
                            'negada' => $query->where('fscs', '00000'),
                            'vencida' => $query->where('validity', '<', now()),
                            'em_processamento' => $query->where('type', 'TCMS')->whereNotNull('fscs'),
                            'pendente' => $query->where('type', 'CRED')->whereNotNull('fscs')->whereNull('concession'),
                            'ativa' => $query->where('type', 'CRED')->whereNotNull('fscs')->whereNotNull('concession')->where('validity', '>=', now()),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Credential $record): string => Pages\EditCredential::getUrl(['record' => $record])),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
