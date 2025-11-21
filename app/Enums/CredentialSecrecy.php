<?php

namespace App\Enums;

enum CredentialSecrecy: string
{
    case RESERVADO = 'R';
    case SECRETO = 'S';

    /**
     * Retorna o label descritivo do nível de sigilo
     */
    public function label(): string
    {
        return match ($this) {
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
            self::RESERVADO->value => self::RESERVADO->label(),
            self::SECRETO->value => self::SECRETO->label(),
        ];
    }
}
