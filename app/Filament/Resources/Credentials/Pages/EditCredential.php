<?php

namespace App\Filament\Resources\Credentials\Pages;

use App\Filament\Resources\Credentials\CredentialResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCredential extends EditRecord
{
    protected static string $resource = CredentialResource::class;

    protected ?string $heading = 'Editar Credencial';

    protected ?string $subheading = 'Modifique os dados da credencial de segurança';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Redirecionar para a listagem após editar a credencial
     * Melhora a experiência do usuário ao confirmar visualmente que a ação foi realizada
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
