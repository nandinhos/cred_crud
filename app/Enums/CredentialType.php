<?php

namespace App\Enums;

enum CredentialType: string
{
    case CRED = 'CRED';
    case TCMS = 'TCMS';

    /**
     * Retorna o label descritivo do tipo
     */
    public function label(): string
    {
        return match ($this) {
            self::CRED => 'Credencial de Segurança',
            self::TCMS => 'Termo de Compromisso e Manutenção de Sigilo',
        };
    }

    /**
     * Retorna todos os valores possíveis como array associativo
     */
    public static function options(): array
    {
        return [
            self::CRED->value => self::CRED->label(),
            self::TCMS->value => self::TCMS->label(),
        ];
    }
}
