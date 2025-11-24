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
     * Ocultar do menu sidebar para perfil consulta
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Apenas admin e super_admin veem no menu
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Verificar se o usuário pode acessar este recurso
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Admin ou super_admin podem acessar tudo
        // Usuários com role 'consulta' NÃO podem acessar diretamente
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Permitir criar credenciais
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Permitir editar credenciais
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Permitir deletar credenciais
     */
    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Verificar se o usuário pode visualizar registros
     */
    public static function canView($record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Todos os roles autorizados podem visualizar
        return $user->hasRole(['admin', 'super_admin', 'consulta']);
    }
}
