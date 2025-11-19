<?php

namespace App\Filament\Resources\Credentials\Pages;

use App\Filament\Resources\Credentials\CredentialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCredential extends CreateRecord
{
    protected static string $resource = CredentialResource::class;

    protected ?string $heading = 'Criar Credencial';

    protected ?string $subheading = 'Cadastre uma nova credencial de segurança';
}
