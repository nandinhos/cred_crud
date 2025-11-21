<?php

namespace App\Filament\Resources;

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
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nome Completo'),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Perfis e Permissões')
                    ->schema([
                        Select::make('roles')
                            ->label('Perfis')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Selecione um ou mais perfis')
                            ->placeholder('Selecione os perfis'),

                        CheckboxList::make('permissions')
                            ->label('Permissões Adicionais')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->helperText('Permissões específicas')
                            ->searchable(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nome'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('E-mail'),

                TextColumn::make('roles.name')
                    ->label('Perfis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'operador' => 'success',
                        'consulta' => 'primary',
                        default => 'gray',
                    })
                    ->separator(', '),

                TextColumn::make('credentials_count')
                    ->counts('credentials')
                    ->label('Credenciais')
                    ->badge()
                    ->color('info'),

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
                    ->url(fn ($record): string => Pages\EditUser::getUrl(['record' => $record]))
                    ->icon('heroicon-o-pencil'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin', 'consulta']);
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin']);
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin']);
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin']);
    }

    public static function canView($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin', 'consulta']);
    }
}
