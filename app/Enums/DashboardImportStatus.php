<?php

namespace App\Enums;

enum DashboardImportStatus: string
{
    case Uploaded = 'uploaded';
    case Reading = 'reading';
    case Mapped = 'mapped';
    case Converted = 'converted';
    case Completed = 'completed';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Enviado',
            self::Reading => 'Lendo arquivo',
            self::Mapped => 'Prévia pronta',
            self::Converted => 'Convertido',
            self::Completed => 'Concluído',
            self::Error => 'Erro',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Uploaded => 'info',
            self::Reading => 'warning',
            self::Mapped => 'purple',
            self::Converted, self::Completed => 'success',
            self::Error => 'danger',
        };
    }
}
