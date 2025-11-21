<?php

namespace App\Enums;

use Carbon\Carbon;

/**
 * Enum para centralizar lógica de cores de badges no Filament
 *
 * Este enum elimina código duplicado e centraliza a lógica de cores
 * usadas em badges nas tabelas do sistema.
 */
enum BadgeColor: string
{
    case Danger = 'danger';
    case Warning = 'warning';
    case Success = 'success';
    case Info = 'info';
    case Primary = 'primary';
    case Gray = 'gray';

    /**
     * Retorna cor para tipo de documento
     *
     * @param  string  $type  Tipo do documento (CRED ou TCMS)
     * @return string Cor do badge
     */
    public static function forType(string $type): string
    {
        return match ($type) {
            'CRED' => self::Info->value,
            'TCMS' => self::Warning->value,
            default => self::Gray->value,
        };
    }

    /**
     * Retorna cor para nível de sigilo
     *
     * @param  string  $secrecy  Nível de sigilo (AR, R ou S)
     * @return string Cor do badge
     */
    public static function forSecrecy(string $secrecy): string
    {
        return match ($secrecy) {
            'AR' => self::Info->value,
            'R' => self::Success->value,
            'S' => self::Danger->value,
            default => self::Gray->value,
        };
    }

    /**
     * Retorna cor para roles/perfis
     *
     * @param  string  $role  Nome do role
     * @return string Cor do badge
     */
    public static function forRole(string $role): string
    {
        return match ($role) {
            'super_admin' => self::Danger->value,
            'admin' => self::Warning->value,
            'operador' => self::Success->value,
            'consulta' => self::Primary->value,
            default => self::Gray->value,
        };
    }

    /**
     * Retorna cor para validade de credencial
     *
     * @param  Carbon|null  $validity  Data de validade
     * @return string Cor do badge
     */
    public static function forValidity(?Carbon $validity): string
    {
        if (! $validity) {
            return self::Gray->value;
        }

        if ($validity->isPast()) {
            return self::Danger->value;
        }

        if (now()->diffInDays($validity) <= 30) {
            return self::Warning->value;
        }

        return self::Success->value;
    }
}
