<?php

namespace App\Filament\Resources;

use App\Enums\BadgeColor;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Usuários';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'usuário';

    protected static ?string $pluralModelLabel = 'usuários';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do Usuário')
                    ->description('Dados pessoais e credenciais de acesso')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nome de Guerra')
                            ->prefixIcon('heroicon-o-user')
                            ->helperText('Nome curto utilizado no sistema'),

                        TextInput::make('full_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nome Completo')
                            ->prefixIcon('heroicon-o-identification')
                            ->helperText('Nome completo do usuário'),

                        Select::make('rank_id')
                            ->label('Posto/Graduação')
                            ->relationship('rank', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon('heroicon-o-star')
                            ->helperText('Selecione o posto ou graduação')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->abbreviation} - {$record->name} ({$record->armed_force})")
                            ->placeholder('Selecione o posto/graduação'),

                        Select::make('office_id')
                            ->label('Unidade Militar')
                            ->relationship('office', 'office')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon('heroicon-o-building-office')
                            ->helperText('Selecione a unidade militar')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->office} - {$record->description}")
                            ->placeholder('Selecione a unidade'),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-envelope'),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-lock-closed'),
                    ])
                    ->columns(2),

                Section::make('Perfis e Permissões')
                    ->description('Defina os perfis e permissões do usuário')
                    ->icon('heroicon-o-shield-check')
                    ->collapsible()
                    ->schema([
                        Select::make('roles')
                            ->label('Perfis')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->prefixIcon('heroicon-o-user-group')
                            ->helperText('Selecione um ou mais perfis')
                            ->placeholder('Selecione os perfis'),

                        CheckboxList::make('permissions')
                            ->label('Permissões Adicionais')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->helperText('Permissões específicas')
                            ->searchable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rank.abbreviation')
                    ->label('Posto/Grad')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->placeholder('N/A')
                    ->tooltip(fn ($record) => $record->rank ? "{$record->rank->name} ({$record->rank->armed_force})" : 'Sem posto/graduação'),

                TextColumn::make('office.office')
                    ->label('Unidade')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('N/A')
                    ->tooltip(fn ($record) => $record->office ? $record->office->description : 'Sem unidade')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nome de Guerra'),

                TextColumn::make('full_name')
                    ->searchable()
                    ->sortable()
                    ->label('Nome Completo')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('E-mail')
                    ->copyable()
                    ->copyMessage('E-mail copiado!')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('roles.name')
                    ->label('Perfis')
                    ->badge()
                    ->color(fn (string $state): string => BadgeColor::forRole($state))
                    ->separator(', '),

                TextColumn::make('credentials_count')
                    ->counts('credentials')
                    ->label('Credenciais')
                    ->badge()
                    ->color('info')
                    ->tooltip('Total de credenciais (incluindo histórico)'),

                TextColumn::make('active_credentials_count')
                    ->label('Ativas')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(fn ($record) => $record->credentials()->whereNull('deleted_at')->count())
                    ->tooltip('Credenciais ativas'),

                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->label('Criado em')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Filtrar por Perfil')
                    ->preload(),

                Filter::make('has_credentials')
                    ->query(fn (Builder $query) => $query->has('credentials'))
                    ->label('Com Credenciais')
                    ->toggle(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('')
                    ->tooltip('Editar')
                    ->url(fn ($record): string => Pages\EditUser::getUrl(['record' => $record]))
                    ->icon('heroicon-m-pencil-square'),
                Action::make('delete')
                    ->label('')
                    ->tooltip('Excluir')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir Usuário')
                    ->modalDescription('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')
                    ->modalSubmitActionLabel('Sim, excluir')
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false); // Remover paginação - mostrar todos os usuários
    }

    public static function getRelations(): array
    {
        return [
            UserResource\RelationManagers\CredentialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Ocultar do menu sidebar - APENAS super admin pode ver
     * Regra: admin NÃO tem acesso à tela de usuários
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // APENAS super admin pode ver menu de usuários
        return $user->hasAnyRole(['super_admin', 'Super Admin']);
    }

    /**
     * Bloquear acesso direto via URL
     * APENAS super admin pode acessar
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'Super Admin']);
    }

    /**
     * Permitir criar usuários - APENAS super admin
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'Super Admin']);
    }

    /**
     * Permitir editar usuários - APENAS super admin
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'Super Admin']);
    }

    /**
     * Permitir deletar usuários - APENAS super admin
     */
    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'Super Admin']);
    }

    public static function canView($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(['admin', 'super_admin', 'Super Admin', 'Administrador', 'consulta', 'Consulta']);
    }
}
