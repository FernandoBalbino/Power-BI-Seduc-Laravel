<?php

namespace App\Enums;

enum DashboardWidgetChartType: string
{
    case Card = 'card';
    case Bar = 'bar';
    case Line = 'line';
    case Pie = 'pie';
    case Donut = 'donut';
    case Area = 'area';
    case Table = 'table';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'Card de resumo',
            self::Bar => 'Barras',
            self::Line => 'Linha',
            self::Pie => 'Pizza',
            self::Donut => 'Donut',
            self::Area => 'Área',
            self::Table => 'Tabela',
        };
    }

    public function apexType(): ?string
    {
        return match ($this) {
            self::Bar => 'bar',
            self::Line => 'line',
            self::Pie => 'pie',
            self::Donut => 'donut',
            self::Area => 'area',
            self::Card,
            self::Table => null,
        };
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
