<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\BadgeColor;
use App\Filament\Resources\Credentials\Schemas\CredentialForm;
use App\Models\Credential;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CredentialsRelationManager extends RelationManager
{
    protected static string $relationship = 'credentials';

    protected static ?string $title = 'Histórico de Credenciais';

    protected static ?string $modelLabel = 'credencial';

    protected static ?string $pluralModelLabel = 'credenciais';

    public function form(Schema $schema): Schema
    {
        // Usar o formulário padrão, mas ocultar o campo de usuário
        $form = CredentialForm::configure($schema);

        // Encontrar e modificar o campo user_id para ser hidden
        $components = $form->getComponents();

        foreach ($components as $component) {
            if (method_exists($component, 'getChildComponents')) {
                $childComponents = $component->getChildComponents();

                foreach ($childComponents as $key => $child) {
                    if (method_exists($child, 'getName') && $child->getName() === 'user_id') {
                        // Substituir o Select por um Hidden com valor fixo
                        $childComponents[$key] = Forms\Components\Hidden::make('user_id')
                            ->default(fn () => $this->getOwnerRecord()->id);
                    }
                }

                // Atualizar os componentes filhos
                $component->schema($childComponents);
            }
        }

        return $form;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informações da Credencial')
                    ->schema([
                        TextEntry::make('fscs')
                            ->label('FSCS')
                            ->weight(FontWeight::Bold)
                            ->copyable()
                            ->copyMessage('FSCS copiado!')
                            ->size(TextSize::Large),

                        TextEntry::make('credential')
                            ->label('Número da Credencial')
                            ->copyable()
                            ->copyMessage('Número copiado!'),

                        TextEntry::make('type')
                            ->label('Tipo')
                            ->badge()
                            ->color(fn ($state): string => BadgeColor::forType($state->value ?? $state)),

                        TextEntry::make('status')
                            ->label('Status Atual')
                            ->badge()
                            ->color(fn ($record): string => $record->status_color),

                        TextEntry::make('secrecy')
                            ->label('Nível de Sigilo')
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
                            }),
                    ])
                    ->columns(2),

                Section::make('Datas Importantes')
                    ->schema([
                        TextEntry::make('concession')
                            ->label('Data de Concessão')
                            ->date('d/m/Y')
                            ->placeholder('Não informada')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('validity')
                            ->label('Data de Validade')
                            ->date('d/m/Y')
                            ->placeholder('Não informada')
                            ->icon('heroicon-o-calendar')
                            ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'success'),

                        TextEntry::make('created_at')
                            ->label('Criada em')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-clock'),

                        TextEntry::make('updated_at')
                            ->label('Última Atualização')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-clock'),

                        TextEntry::make('deleted_at')
                            ->label('Deletada em')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Credencial ativa')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn ($record) => $record->trashed()),
                    ])
                    ->columns(2),

                Section::make('Observações')
                    ->schema([
                        TextEntry::make('observation')
                            ->label('')
                            ->placeholder('Sem observações')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->observation))
                    ->collapsible(),

                Section::make('Informações do Usuário')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Usuário')
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-o-user'),

                        TextEntry::make('user.rank.name')
                            ->label('Posto/Graduação')
                            ->placeholder('Não informado')
                            ->icon('heroicon-o-star'),

                        TextEntry::make('user.office.name')
                            ->label('Unidade')
                            ->placeholder('Não informado')
                            ->icon('heroicon-o-building-office'),

                        TextEntry::make('user.email')
                            ->label('E-mail')
                            ->copyable()
                            ->icon('heroicon-o-envelope'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed())
            ->recordTitleAttribute('fscs')
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
                    ->color(fn ($record): string => $record->status_color),

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
                    }),

                Tables\Columns\TextColumn::make('credential')
                    ->label('Número')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('concession')
                    ->label('Concessão')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('validity')
                    ->label('Validade')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => BadgeColor::forValidity($state ? \Carbon\Carbon::parse($state) : null)),

                Tables\Columns\IconColumn::make('is_deleted')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-trash')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->getStateUsing(fn (Credential $record): bool => $record->trashed())
                    ->tooltip(fn (Credential $record): string => $record->trashed() ? 'Deletada' : 'Ativa'),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deletada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'CRED' => 'CRED',
                        'TCMS' => 'TCMS',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Válida' => 'Válida',
                        'Pendente' => 'Pendente',
                        'Em Processamento' => 'Em Processamento',
                        'Vencida' => 'Vencida',
                        'Negada' => 'Negada',
                        'Pane - Verificar' => 'Pane - Verificar',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Credencial criada')
                            ->body('A credencial foi criada com sucesso.')
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Credencial excluída')
                            ->body('A credencial foi movida para a lixeira.')
                    ),
                RestoreAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Credencial restaurada')
                            ->body('A credencial foi restaurada com sucesso.')
                    ),
                ForceDeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Excluir Permanentemente')
                    ->modalDescription('Esta ação é irreversível. A credencial será permanentemente excluída.')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Credencial excluída permanentemente')
                            ->body('A credencial foi removida permanentemente do sistema.')
                    ),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Nenhuma credencial encontrada')
            ->emptyStateDescription('Este usuário ainda não possui credenciais registradas.')
            ->emptyStateIcon('heroicon-o-identification');
    }
}
