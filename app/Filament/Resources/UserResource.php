<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = "Usuários";

    protected static ?string $modelLabel = "usuário";

    protected static ?string $pluralModelLabel = "usuários";

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Informações do Usuário")
                    ->schema([
                        TextInput::make("name")
                            ->required()
                            ->maxLength(255)
                            ->label("Nome Completo"),

                        TextInput::make("email")
                            ->label("E-mail")
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make("password")
                            ->label("Senha")
                            ->password()
                            ->required(fn (string $operation): bool => $operation === "create")
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->revealable()
                            ->minLength(8)
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make("Perfis e Permissões")
                    ->schema([
                        Select::make("roles")
                            ->label("Perfis")
                            ->multiple()
                            ->relationship("roles", "name")
                            ->preload()
                            ->searchable()
                            ->helperText("Selecione um ou mais perfis")
                            ->placeholder("Selecione os perfis"),

                        CheckboxList::make("permissions")
                            ->label("Permissões Adicionais")
                            ->relationship("permissions", "name")
                            ->columns(3)
                            ->helperText("Permissões específicas")
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
                TextColumn::make("name")
                    ->searchable()
                    ->sortable()
                    ->label("Nome"),

                TextColumn::make("email")
                    ->searchable()
                    ->sortable()
                    ->label("E-mail"),

                BadgeColumn::make("roles.name")
                    ->label("Perfis")
                    ->colors([
                        "danger" => "Super Admin",
                        "warning" => "Administrador",
                        "success" => "Operador",
                        "primary" => "Consulta",
                    ])
                    ->separator(", "),

                TextColumn::make("credentials_count")
                    ->counts("credentials")
                    ->label("Credenciais")
                    ->badge()
                    ->color("info"),

                TextColumn::make("created_at")
                    ->dateTime("d/m/Y H:i")
                    ->sortable()
                    ->label("Criado em")
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make("roles")
                    ->relationship("roles", "name")
                    ->label("Filtrar por Perfil")
                    ->preload(),

                Filter::make("has_credentials")
                    ->query(fn (Builder $query) => $query->has("credentials"))
                    ->label("Com Credenciais")
                    ->toggle(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListUsers::route("/"),
            "create" => Pages\CreateUser::route("/create"),
            "edit" => Pages\EditUser::route("/{record}/edit"),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can("view_users") ?? false;
    }
}
