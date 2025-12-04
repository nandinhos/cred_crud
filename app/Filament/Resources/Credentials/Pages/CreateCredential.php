<?php

namespace App\Filament\Resources\Credentials\Pages;

use App\Filament\Resources\Credentials\CredentialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCredential extends CreateRecord
{
    protected static string $resource = CredentialResource::class;

    protected ?string $heading = 'Criar Credencial';

    protected ?string $subheading = 'Cadastre uma nova credencial de segurança';

    /**
     * Redirecionar para a listagem após criar a credencial
     * Melhora a experiência do usuário ao confirmar visualmente que a ação foi realizada
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
