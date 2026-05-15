<?php

namespace App\Enums;

enum DashboardRelationshipAggregation: string
{
    case Sum = 'sum';
    case Avg = 'avg';
    case Count = 'count';
    case Min = 'min';
    case Max = 'max';

    public function label(): string
    {
        return match ($this) {
            self::Sum => 'Soma',
            self::Avg => 'Média',
            self::Count => 'Contagem',
            self::Min => 'Mínimo',
            self::Max => 'Máximo',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $aggregation) => ['value' => $aggregation->value, 'label' => $aggregation->label()],
            self::cases()
        );
    }
}
