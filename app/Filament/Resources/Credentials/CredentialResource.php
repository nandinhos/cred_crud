<?php

namespace App\Filament\Resources\Credentials;

use App\Filament\Resources\Credentials\Pages\CreateCredential;
use App\Filament\Resources\Credentials\Pages\EditCredential;
use App\Filament\Resources\Credentials\Pages\ListCredentials;
use App\Filament\Resources\Credentials\Schemas\CredentialForm;
use App\Filament\Resources\Credentials\Tables\CredentialsTable;
use App\Models\Credential;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CredentialResource extends Resource
{
    protected static ?string $model = Credential::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Credenciais';

    protected static ?string $modelLabel = 'credencial';

    protected static ?string $pluralModelLabel = 'credenciais';

    public static function form(Schema $schema): Schema
    {
        return CredentialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CredentialsTable::configure($table);
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

    /**
     * Mostrar menu para quem pode visualizar credenciais
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    /**
     * Verificar se o usuário pode acessar este recurso
     * Delega para a CredentialPolicy via método viewAny
     */
    public static function canAccess(): bool
    {
        return static::can('viewAny');
    }

    /**
     * Permitir criar credenciais
     * Delega para a CredentialPolicy
     */
    public static function canCreate(): bool
    {
        return static::can('create');
    }

    /**
     * Permitir editar credenciais
     * Delega para a CredentialPolicy
     */
    public static function canEdit($record): bool
    {
        return static::can('update', $record);
    }

    /**
     * Permitir deletar credenciais
     * Delega para a CredentialPolicy
     */
    public static function canDelete($record): bool
    {
        return static::can('delete', $record);
    }

    /**
     * Verificar se o usuário pode visualizar registros
     * Delega para a CredentialPolicy
     */
    public static function canView($record): bool
    {
        return static::can('view', $record);
    }
}
