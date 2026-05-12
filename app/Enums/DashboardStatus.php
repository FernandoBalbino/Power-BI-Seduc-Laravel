<?php

namespace App\Enums;

enum DashboardStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Ready = 'ready';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Processing => 'Processando',
            self::Ready => 'Pronto',
            self::Error => 'Erro',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Processing => 'info',
            self::Ready => 'success',
            self::Error => 'danger',
        };
    }
}
