<?php

namespace App\Services;

use App\Enums\DashboardColumnType;
use App\Enums\DashboardRelationshipAggregation;
use App\Enums\DashboardWidgetChartType;
use App\Models\Dashboard;
use App\Models\DashboardColumn;
use App\Models\DashboardWidget;
use Illuminate\Support\Collection;

class DashboardQueryService
{
    /**
     * @return array<string, mixed>
     */
    public function dataForWidget(DashboardWidget $widget): array
    {
        $dashboard = $widget->dashboard()->with(['columns', 'rows'])->firstOrFail();
        $config = $widget->config_json ?? [];
        $baseColumn = $this->columnFromConfig($dashboard, $config['base_column_id'] ?? null);
        $valueColumn = $this->columnFromConfig($dashboard, $config['value_column_id'] ?? null);
        $aggregation = DashboardRelationshipAggregation::from($config['aggregation'] ?? DashboardRelationshipAggregation::Count->value);
        $limit = (int) ($config['limit'] ?? 10);
        $sort = (string) ($config['sort'] ?? 'desc');

        return match ($widget->chart_type) {
            DashboardWidgetChartType::Card => $this->cardData($dashboard, $valueColumn, $aggregation),
            DashboardWidgetChartType::Table => $this->tableData($dashboard, $baseColumn, $valueColumn, $aggregation, $limit, $sort),
            DashboardWidgetChartType::Pie,
            DashboardWidgetChartType::Donut => $this->pieData($dashboard, $baseColumn, $valueColumn, $aggregation, $limit, $sort),
            DashboardWidgetChartType::Bar,
            DashboardWidgetChartType::Line,
            DashboardWidgetChartType::Area => $this->seriesData($dashboard, $baseColumn, $valueColumn, $aggregation, $limit, $sort),
        };
    }

    /**
     * @return array{value: float|int, formatted: string, label: string, aggregation: string}
     */
    public function cardData(Dashboard $dashboard, ?DashboardColumn $valueColumn, DashboardRelationshipAggregation|string $aggregation): array
    {
        $aggregation = $aggregation instanceof DashboardRelationshipAggregation
            ? $aggregation
            : DashboardRelationshipAggregation::from($aggregation);
        $rows = $this->rows($dashboard);
        $value = $this->aggregateRows($rows, $valueColumn, $aggregation);

        return [
            'value' => $value,
            'formatted' => $this->formatValue($value, $valueColumn),
            'label' => $valueColumn?->displayName() ?? 'Quantidade de registros',
            'aggregation' => $aggregation->label(),
        ];
    }

    /**
     * @return array{categories: array<int, string>, series: array<int, array{name: string, data: array<int, float|int>}>, rows: array<int, array{label: string, value: float|int, formatted: string}>}
     */
    public function seriesData(
        Dashboard $dashboard,
        ?DashboardColumn $baseColumn,
        ?DashboardColumn $valueColumn,
        DashboardRelationshipAggregation|string $aggregation,
        int $limit = 10,
        string $sort = 'desc'
    ): array {
        $rows = $this->aggregateBy($dashboard, $baseColumn, $valueColumn, $aggregation, $limit, $sort);

        return [
            'categories' => array_column($rows, 'label'),
            'series' => [[
                'name' => $valueColumn?->displayName() ?? 'Quantidade de registros',
                'data' => array_column($rows, 'value'),
            ]],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{labels: array<int, string>, series: array<int, float|int>, rows: array<int, array{label: string, value: float|int, formatted: string}>}
     */
    public function pieData(
        Dashboard $dashboard,
        ?DashboardColumn $baseColumn,
        ?DashboardColumn $valueColumn,
        DashboardRelationshipAggregation|string $aggregation,
        int $limit = 10,
        string $sort = 'desc'
    ): array {
        $rows = $this->aggregateBy($dashboard, $baseColumn, $valueColumn, $aggregation, $limit, $sort);

        return [
            'labels' => array_column($rows, 'label'),
            'series' => array_column($rows, 'value'),
            'rows' => $rows,
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array{label: string, value: float|int, formatted: string}>}
     */
    public function tableData(
        Dashboard $dashboard,
        ?DashboardColumn $baseColumn,
        ?DashboardColumn $valueColumn,
        DashboardRelationshipAggregation|string $aggregation,
        int $limit = 10,
        string $sort = 'desc'
    ): array {
        return [
            'headers' => [$baseColumn?->displayName() ?? 'Grupo', $valueColumn?->displayName() ?? 'Quantidade'],
            'rows' => $this->aggregateBy($dashboard, $baseColumn, $valueColumn, $aggregation, $limit, $sort),
        ];
    }

    /**
     * @return array<int, array{label: string, value: float|int, formatted: string}>
     */
    public function aggregateBy(
        Dashboard $dashboard,
        ?DashboardColumn $baseColumn,
        ?DashboardColumn $valueColumn,
        DashboardRelationshipAggregation|string $aggregation,
        int $limit = 10,
        string $sort = 'desc'
    ): array {
        $aggregation = $aggregation instanceof DashboardRelationshipAggregation
            ? $aggregation
            : DashboardRelationshipAggregation::from($aggregation);
        $rows = $this->rows($dashboard);

        if (! $baseColumn) {
            $value = $this->aggregateRows($rows, $valueColumn, $aggregation);

            return [[
                'label' => $valueColumn?->displayName() ?? 'Registros',
                'value' => $value,
                'formatted' => $this->formatValue($value, $valueColumn),
            ]];
        }

        $groups = $rows->groupBy(function (array $row) use ($baseColumn): string {
            $value = $row[$baseColumn->normalized_name] ?? null;

            return $this->normalizeGroupLabel($value);
        });

        $result = $groups
            ->map(function (Collection $groupRows, string $label) use ($valueColumn, $aggregation): array {
                $value = $this->aggregateRows($groupRows, $valueColumn, $aggregation);

                return [
                    'label' => $label,
                    'value' => $value,
                    'formatted' => $this->formatValue($value, $valueColumn),
                ];
            });

        $result = $sort === 'asc'
            ? $result->sortBy('value')
            : $result->sortByDesc('value');

        return $result
            ->take(max(1, $limit))
            ->values()
            ->all();
    }

    public function aggregateRows(
        Collection $rows,
        ?DashboardColumn $valueColumn,
        DashboardRelationshipAggregation $aggregation
    ): float|int {
        if ($aggregation === DashboardRelationshipAggregation::Count || ! $valueColumn) {
            return $rows->count();
        }

        $values = $rows
            ->map(fn (array $row) => $row[$valueColumn->normalized_name] ?? null)
            ->filter(fn (mixed $value) => is_numeric($value))
            ->map(fn (mixed $value) => (float) $value)
            ->values();

        if ($values->isEmpty()) {
            return 0;
        }

        return match ($aggregation) {
            DashboardRelationshipAggregation::Sum => round($values->sum(), 2),
            DashboardRelationshipAggregation::Avg => round($values->avg(), 4),
            DashboardRelationshipAggregation::Min => round($values->min(), 4),
            DashboardRelationshipAggregation::Max => round($values->max(), 4),
            DashboardRelationshipAggregation::Count => $rows->count(),
        };
    }

    public function formatValue(float|int $value, ?DashboardColumn $column): string
    {
        return match ($column?->type) {
            DashboardColumnType::Money => 'R$ '.number_format((float) $value, 2, ',', '.'),
            DashboardColumnType::Percentage => number_format((float) $value, 2, ',', '.').'%',
            default => is_float($value)
                ? number_format($value, 2, ',', '.')
                : number_format($value, 0, ',', '.'),
        };
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function rows(Dashboard $dashboard): Collection
    {
        return $dashboard->rows()
            ->get()
            ->map(fn ($row) => $row->data_json ?? []);
    }

    private function columnFromConfig(Dashboard $dashboard, mixed $columnId): ?DashboardColumn
    {
        if (! $columnId) {
            return null;
        }

        return $dashboard->columns->firstWhere('id', (int) $columnId)
            ?? $dashboard->columns()->find((int) $columnId);
    }

    private function normalizeGroupLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Sem informação';
        }

        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }

        return (string) $value;
    }
}
