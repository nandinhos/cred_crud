<?php

namespace App\Filament\Resources\Credentials\Tables;

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

                Tables\Columns\TextColumn::make('secrecy')
                    ->label('Sigilo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'O' => 'info',
                        'R' => 'success',
                        'S' => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'O' => 'Ostensivo',
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
                    ->color(function ($state) {
                        if (! $state) {
                            return 'gray';
                        }
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

                SelectFilter::make('secrecy')
                    ->label('Nível de Sigilo')
                    ->options([
                        'O' => 'Ostensivo',
                        'R' => 'Reservado',
                        'S' => 'Secreto',
                    ]),

                Filter::make('validity_status')
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
