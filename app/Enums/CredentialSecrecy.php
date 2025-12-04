<?php

namespace App\Enums;

enum CredentialSecrecy: string
{
    case ACESSO_RESTRITO = 'AR';
    case RESERVADO = 'R';
    case SECRETO = 'S';

    /**
     * Retorna o label descritivo do nível de sigilo
     */
    public function label(): string
    {
        return match ($this) {
            self::ACESSO_RESTRITO => 'Acesso Restrito',
            self::RESERVADO => 'Reservado',
            self::SECRETO => 'Secreto',
        };
    }

    /**
     * Retorna a cor do badge para uso no Filament
     */
    public function color(): string
    {
        return match ($this) {
            self::ACESSO_RESTRITO => 'indigo',
            self::RESERVADO => 'success',
            self::SECRETO => 'danger',
        };
    }

    /**
     * Retorna todos os valores possíveis como array associativo
     */
    public static function options(): array
    {
        return [
            self::ACESSO_RESTRITO->value => self::ACESSO_RESTRITO->label(),
            self::RESERVADO->value => self::RESERVADO->label(),
            self::SECRETO->value => self::SECRETO->label(),
        ];
    }

    /**
     * Retorna as opções de sigilo baseadas no tipo de credencial
     */
    public static function optionsForType(CredentialType $type): array
    {
        return match ($type) {
            CredentialType::CRED => [
                self::RESERVADO->value => self::RESERVADO->label(),
                self::SECRETO->value => self::SECRETO->label(),
            ],
            CredentialType::TCMS => [
                self::ACESSO_RESTRITO->value => self::ACESSO_RESTRITO->label(),
            ],
        };
    }

    /**
     * Valida se o sigilo é válido para o tipo de credencial
     */
    public static function isValidForType(string $secrecy, CredentialType $type): bool
    {
        $validOptions = array_keys(self::optionsForType($type));

        return in_array($secrecy, $validOptions);
    }
}
