<?php

namespace App\Filament\Resources\Credentials\Tables;

use App\Enums\BadgeColor;
use App\Filament\Resources\Credentials\Pages;
use App\Models\Credential;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
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
                    ->copyMessageDuration(1500)
                    ->placeholder('-')
                    ->default('-'),

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
                            'AR' => 'Acesso Restrito',
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
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->placeholder('-')
                    ->default('-'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(function (Credential $record): string {
                        $name = $record->user?->name ?? 'N/A';
                        $office = $record->user?->office?->name ?? '';

                        if ($office) {
                            return $name.'<br><span style="color: #6b7280; font-style: italic; font-size: 0.75rem;">'.$office.'</span>';
                        }

                        return $name;
                    })
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

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->getStateUsing(fn (Credential $record): bool => $record->trashed())
                    ->tooltip(fn (Credential $record): string => $record->trashed() ? 'Deletada' : 'Ativa')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deletada em')
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
                        'AR' => 'Acesso Restrito',
                        'R' => 'Reservado',
                        'S' => 'Secreto',
                    ]),

                Filter::make('status')
                    ->label('Status da Credencial')
                    ->form([
                        Forms\Components\Select::make('status_filter')
                            ->label('Status')
                            ->options([
                                'pane' => 'Pane - Verificar',
                                'em_processamento' => 'Em Processamento',
                                'vencida' => 'Vencida',
                                'pendente' => 'Pendente',
                                'valida' => 'Válida',
                                'negada' => 'Negada',
                            ])
                            ->placeholder('Todos os status'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['status_filter'])) {
                            return $query;
                        }

                        return match ($data['status_filter']) {
                            'pane' => $query->whereNull('fscs')
                                ->where('type', 'TCMS')
                                ->where(function ($q) {
                                    $q->whereNull('credential')
                                        ->orWhere('credential', 'NOT LIKE', '%TCMS%');
                                }),
                            'negada' => $query->where('fscs', '00000'),
                            'vencida' => $query->where('validity', '<', now()),
                            'em_processamento' => $query->where('type', 'TCMS')->whereNotNull('fscs'),
                            'pendente' => $query->where('type', 'CRED')->whereNotNull('fscs')->whereNull('concession'),
                            'valida' => $query->where('type', 'CRED')->whereNotNull('fscs')->whereNotNull('concession')->where('validity', '>=', now()),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('')
                    ->tooltip('Visualizar'),
                Action::make('edit')
                    ->label('')
                    ->tooltip('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Credential $record): string => Pages\EditCredential::getUrl(['record' => $record])),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Excluir')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Credencial excluída')
                            ->body('A credencial foi movida para a lixeira.')
                    ),
                RestoreAction::make()
                    ->label('')
                    ->tooltip('Restaurar')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Credencial restaurada')
                            ->body('A credencial foi restaurada com sucesso.')
                    ),
                ForceDeleteAction::make()
                    ->label('')
                    ->tooltip('Excluir Permanentemente')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir Credencial Permanentemente')
                    ->modalDescription('Atenção! Esta ação é irreversível. A credencial será permanentemente excluída do sistema.')
                    ->modalSubmitActionLabel('Sim, excluir permanentemente')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Credencial excluída permanentemente')
                            ->body('A credencial foi removida permanentemente do sistema.')
                    ),
            ])
            ->defaultSort('validity', 'asc') // Ordenar por validade (mais urgentes primeiro)
            ->paginated(false) // Remover paginação - mostrar todos os registros
            ->modifyQueryUsing(function ($query) {
                // Ordenação visual organizada:
                // 1) Pane - Verificar (prioridade 0) - SEMPRE PRIMEIRO, independente do tipo
                //    Inclui: TCMS sem FSCS, CRED sem FSCS, TCMS com FSCS mas SEM concessão
                // 2) Em Processamento (prioridade 1) - TCMS com FSCS e COM concessão (ordenados por data)
                // 3) Vencidas até Válidas ordenadas por data de validade (prioridade 2)
                // 4) Negadas por último (prioridade 3)
                return $query->selectRaw('
                    credentials.*,
                    CASE
                        -- PANE: Casos que não se encaixam nas regras (SEMPRE PRIMEIRO)
                        -- TCMS sem FSCS e sem "TCMS" no credential
                        WHEN fscs IS NULL AND type = "TCMS" AND (credential IS NULL OR credential NOT LIKE "%TCMS%") THEN 0
                        -- CRED sem FSCS
                        WHEN fscs IS NULL AND type = "CRED" THEN 0
                        -- TCMS com FSCS mas SEM concessão (PANE)
                        WHEN fscs IS NOT NULL AND fscs != "00000" AND type = "TCMS" AND concession IS NULL THEN 0
                        -- Em Processamento: TCMS com FSCS válido (não "00000") e COM concessão
                        WHEN fscs IS NOT NULL AND fscs != "00000" AND type = "TCMS" AND concession IS NOT NULL THEN 1
                        -- Negadas: FSCS = "00000" (por último)
                        WHEN fscs = "00000" THEN 3
                        -- Demais casos: ordenação por validade
                        ELSE 2
                    END as sort_priority
                ')
                    ->orderBy('sort_priority', 'asc')
                    ->orderByRaw('CASE WHEN sort_priority = 1 THEN concession END ASC')
                    ->orderByRaw('CASE WHEN sort_priority = 2 THEN validity END ASC')
                    ->orderBy('created_at', 'desc');
            })
            ->recordClasses(function (Credential $record): ?string {
                // PRIORIDADE 0: Credencial Deletada - Cinza claro com opacidade
                if ($record->trashed()) {
                    return 'bg-gray-100 hover:bg-gray-200 transition-colors duration-150 opacity-60';
                }

                // PRIORIDADE 1: Credenciais com status "Pane - Verificar" ficam com fundo vermelho vivo
                if ($record->status === 'Pane - Verificar') {
                    return 'bg-red-200 hover:bg-red-300 transition-colors duration-150 border-l-4 border-red-600';
                }

                // Credencial Negada - Cinza mais escuro
                if ($record->fscs === '00000') {
                    return 'bg-gray-200 hover:bg-gray-300 transition-colors duration-150';
                }

                // Credencial Pendente (sem concessão) - Índigo claro
                if (! $record->concession) {
                    return 'bg-indigo-100 hover:bg-indigo-200 transition-colors duration-150';
                }

                // Credenciais sem validade
                if (! $record->validity) {
                    return null;
                }

                $validity = $record->validity;

                // Vencida - Vermelho mais forte
                if ($validity->isPast()) {
                    return 'bg-red-100 hover:bg-red-200 transition-colors duration-150';
                }

                $daysUntilExpiry = now()->diffInDays($validity, false);

                // Gradiente de 60 dias até vencimento (amarelo → laranja → vermelho)

                // Crítica (1-15 dias) - Laranja/Vermelho forte
                if ($daysUntilExpiry <= 15) {
                    return 'bg-orange-200 hover:bg-orange-300 transition-colors duration-150';
                }

                // Atenção (16-30 dias) - Laranja médio
                if ($daysUntilExpiry <= 30) {
                    return 'bg-orange-100 hover:bg-orange-200 transition-colors duration-150';
                }

                // Alerta (31-45 dias) - Amarelo forte
                if ($daysUntilExpiry <= 45) {
                    return 'bg-yellow-200 hover:bg-yellow-300 transition-colors duration-150';
                }

                // Início do gradiente (46-60 dias) - Amarelo médio
                if ($daysUntilExpiry <= 60) {
                    return 'bg-yellow-100 hover:bg-yellow-200 transition-colors duration-150';
                }

                // Normal (> 60 dias) - Sem cor especial
                return null;
            })
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Credenciais excluídas')
                                ->body('As credenciais foram movidas para a lixeira.')
                        ),
                    RestoreBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Credenciais restauradas')
                                ->body('As credenciais foram restauradas com sucesso.')
                        ),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Excluir Credenciais Permanentemente')
                        ->modalDescription('Atenção! Esta ação é irreversível. As credenciais selecionadas serão permanentemente excluídas do sistema.')
                        ->modalSubmitActionLabel('Sim, excluir permanentemente')
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Credenciais excluídas permanentemente')
                                ->body('As credenciais foram removidas permanentemente do sistema.')
                        ),
                ]),
            ]);
    }
}
