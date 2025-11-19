<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
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

                TextColumn::make("updated_at")
                    ->dateTime("d/m/Y H:i")
                    ->sortable()
                    ->label("Atualizado em")
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }
}
