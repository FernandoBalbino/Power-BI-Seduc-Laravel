<?php

namespace App\Enums;

enum DashboardColumnType: string
{
    case ShortText = 'short_text';
    case LongText = 'long_text';
    case Number = 'number';
    case Money = 'money';
    case Percentage = 'percentage';
    case Date = 'date';
    case Category = 'category';
    case Identifier = 'identifier';
    case Boolean = 'boolean';
    case Ignore = 'ignore';

    public function label(): string
    {
        return match ($this) {
            self::ShortText => 'Texto curto',
            self::LongText => 'Texto longo',
            self::Number => 'Número',
            self::Money => 'Dinheiro',
            self::Percentage => 'Porcentagem',
            self::Date => 'Data',
            self::Category => 'Opção/Categoria',
            self::Identifier => 'Código/Identificador',
            self::Boolean => 'Sim/Não',
            self::Ignore => 'Ignorar coluna',
        };
    }

    public function isDimensional(): bool
    {
        return in_array($this, [
            self::ShortText,
            self::Category,
            self::Identifier,
            self::Date,
        ], true);
    }

    public function isMetric(): bool
    {
        return in_array($this, [
            self::Number,
            self::Money,
            self::Percentage,
        ], true);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases()
        );
    }
}
