<?php

namespace App\Enums;

enum DashboardRelationshipType: string
{
    case Auto = 'auto';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Auto => 'Automático',
            self::Manual => 'Manual',
        };
    }
}
