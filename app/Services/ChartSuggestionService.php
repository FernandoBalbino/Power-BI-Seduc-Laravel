<?php

namespace App\Services;

use App\Enums\DashboardColumnType;
use App\Enums\DashboardRelationshipAggregation;
use App\Enums\DashboardWidgetChartType;
use App\Models\Dashboard;
use App\Models\DashboardColumn;
use App\Models\DashboardRelationship;
use App\Models\DashboardWidget;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ChartSuggestionService
{
    /**
     * @return array<int, array{
     *     title: string,
     *     chart_type: string,
     *     config_json: array<string, mixed>,
     *     width: int,
     *     height: int,
     *     reason: string
     * }>
     */
    public function suggest(Dashboard $dashboard): array
    {
        $dashboard->loadMissing(['columns', 'validRelationships.baseColumn', 'validRelationships.relatedColumn']);
        $suggestions = [];

        if ($dashboard->rows()->exists()) {
            $suggestions[] = $this->buildSuggestion(
                title: 'Total de registros',
                chartType: DashboardWidgetChartType::Card,
                baseColumn: null,
                valueColumn: null,
                aggregation: DashboardRelationshipAggregation::Count,
                reason: 'Card de resumo para acompanhar o volume total de linhas importadas.',
                width: 3,
                height: 2
            );
        }

        foreach ($dashboard->columns->filter(fn (DashboardColumn $column) => $column->isMetric())->take(3) as $metricColumn) {
            $aggregation = $metricColumn->type === DashboardColumnType::Percentage
                ? DashboardRelationshipAggregation::Avg
                : DashboardRelationshipAggregation::Sum;

            $suggestions[] = $this->buildSuggestion(
                title: ($aggregation === DashboardRelationshipAggregation::Avg ? 'Média de ' : 'Total de ').$metricColumn->displayName(),
                chartType: DashboardWidgetChartType::Card,
                baseColumn: null,
                valueColumn: $metricColumn,
                aggregation: $aggregation,
                reason: 'Card de resumo para acompanhar '.$metricColumn->displayName().'.',
                width: 3,
                height: 2
            );
        }

        foreach ($dashboard->validRelationships as $relationship) {
            $chartType = $this->chartTypeForRelationship($relationship);

            if (! $chartType) {
                continue;
            }

            $suggestions[] = $this->buildSuggestion(
                title: $this->titleForRelationship($relationship, $chartType),
                chartType: $chartType,
                baseColumn: $relationship->baseColumn,
                valueColumn: $relationship->relatedColumn,
                aggregation: $relationship->aggregation,
                reason: $this->reasonForRelationship($relationship, $chartType),
                relationship: $relationship,
                width: $chartType === DashboardWidgetChartType::Table ? 12 : 6,
                height: $chartType === DashboardWidgetChartType::Table ? 4 : 4
            );
        }

        $tableRelationship = $dashboard->validRelationships
            ->first(fn (DashboardRelationship $relationship) => $relationship->relatedColumn?->isMetric());

        if ($tableRelationship) {
            $suggestions[] = $this->buildSuggestion(
                title: 'Tabela resumida por '.$tableRelationship->baseColumn->displayName(),
                chartType: DashboardWidgetChartType::Table,
                baseColumn: $tableRelationship->baseColumn,
                valueColumn: $tableRelationship->relatedColumn,
                aggregation: $tableRelationship->aggregation,
                reason: 'Tabela para conferir os principais registros agregados.',
                relationship: $tableRelationship,
                width: 12,
                height: 4
            );
        }

        return collect($suggestions)
            ->unique(fn (array $suggestion) => $suggestion['title'].'|'.$suggestion['chart_type'])
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * @return array<int, DashboardWidget>
     */
    public function createAutomaticWidgets(Dashboard $dashboard): array
    {
        $created = [];
        $position = $this->nextPosition($dashboard);

        foreach ($this->suggest($dashboard) as $suggestion) {
            if ($this->widgetExists($dashboard, $suggestion)) {
                continue;
            }

            $widget = DashboardWidget::query()->create([
                'dashboard_id' => $dashboard->id,
                'title' => $suggestion['title'],
                'chart_type' => $suggestion['chart_type'],
                'config_json' => $suggestion['config_json'],
                'position_x' => $position['x'],
                'position_y' => $position['y'],
                'width' => $suggestion['width'],
                'height' => $suggestion['height'],
            ]);

            $created[] = $widget;
            $position = $this->advancePosition($position, $suggestion['width']);
        }

        return $created;
    }

    public function createManualWidget(
        Dashboard $dashboard,
        string $title,
        DashboardWidgetChartType|string $chartType,
        array $config,
        int $width = 6,
        int $height = 4
    ): DashboardWidget {
        $chartType = $chartType instanceof DashboardWidgetChartType
            ? $chartType
            : DashboardWidgetChartType::from($chartType);
        $this->validateConfig($dashboard, $chartType, $config);
        $position = $this->nextPosition($dashboard);

        return DashboardWidget::query()->create([
            'dashboard_id' => $dashboard->id,
            'title' => Str::squish($title),
            'chart_type' => $chartType,
            'config_json' => $config,
            'position_x' => $position['x'],
            'position_y' => $position['y'],
            'width' => $width,
            'height' => $height,
        ]);
    }

    public function validateConfig(Dashboard $dashboard, DashboardWidgetChartType $chartType, array $config): void
    {
        $aggregation = DashboardRelationshipAggregation::from($config['aggregation'] ?? DashboardRelationshipAggregation::Count->value);
        $baseColumn = $this->column($dashboard, $config['base_column_id'] ?? null);
        $valueColumn = $this->column($dashboard, $config['value_column_id'] ?? null);

        if ($chartType !== DashboardWidgetChartType::Card && ! $baseColumn) {
            throw ValidationException::withMessages([
                'manualGroupingColumnId' => 'Escolha uma coluna de agrupamento.',
            ]);
        }

        if ($aggregation !== DashboardRelationshipAggregation::Count && ! $valueColumn) {
            throw ValidationException::withMessages([
                'manualValueColumnId' => 'Para usar Soma, Média, Mínimo ou Máximo, escolha uma coluna de valor. Para contar linhas, use Contagem.',
            ]);
        }

        if ($baseColumn && $valueColumn && $baseColumn->id === $valueColumn->id) {
            throw ValidationException::withMessages([
                'manualValueColumnId' => 'A coluna de valor deve ser diferente da coluna de agrupamento.',
            ]);
        }

        if (in_array($aggregation, [DashboardRelationshipAggregation::Sum, DashboardRelationshipAggregation::Avg], true)
            && $valueColumn && ! $valueColumn->isMetric()) {
            throw ValidationException::withMessages([
                'manualAggregation' => 'Soma e média precisam de Número, Dinheiro ou Porcentagem.',
            ]);
        }
    }

    private function chartTypeForRelationship(DashboardRelationship $relationship): ?DashboardWidgetChartType
    {
        $baseColumn = $relationship->baseColumn;
        $relatedColumn = $relationship->relatedColumn;

        if ($relationship->aggregation === DashboardRelationshipAggregation::Count) {
            $isStatusOrCategory = $baseColumn->type === DashboardColumnType::Category
                || Str::contains(Str::of($baseColumn->displayName())->ascii()->lower()->toString(), 'status');

            return $isStatusOrCategory
                ? DashboardWidgetChartType::Donut
                : DashboardWidgetChartType::Pie;
        }

        if ($baseColumn->type === DashboardColumnType::Date && $relatedColumn?->isMetric()) {
            return DashboardWidgetChartType::Line;
        }

        if ($relatedColumn?->isMetric()) {
            return DashboardWidgetChartType::Bar;
        }

        return null;
    }

    private function titleForRelationship(DashboardRelationship $relationship, DashboardWidgetChartType $chartType): string
    {
        $baseName = $relationship->baseColumn->displayName();
        $relatedName = $relationship->relatedColumn?->displayName() ?? 'Quantidade';

        if ($chartType === DashboardWidgetChartType::Donut) {
            return $baseName.' por quantidade';
        }

        if ($chartType === DashboardWidgetChartType::Line) {
            return 'Evolução de '.$relatedName;
        }

        return $relatedName.' por '.$baseName;
    }

    private function reasonForRelationship(DashboardRelationship $relationship, DashboardWidgetChartType $chartType): string
    {
        return match ($chartType) {
            DashboardWidgetChartType::Bar => 'Compara valores entre categorias ou textos curtos.',
            DashboardWidgetChartType::Line => 'Mostra evolução por data ou período.',
            DashboardWidgetChartType::Pie,
            DashboardWidgetChartType::Donut => 'Mostra distribuição proporcional dos registros.',
            DashboardWidgetChartType::Table => 'Organiza o resumo em linhas para conferência.',
            default => 'Sugestão criada a partir dos relacionamentos salvos.',
        };
    }

    private function buildSuggestion(
        string $title,
        DashboardWidgetChartType $chartType,
        ?DashboardColumn $baseColumn,
        ?DashboardColumn $valueColumn,
        DashboardRelationshipAggregation $aggregation,
        string $reason,
        ?DashboardRelationship $relationship = null,
        int $width = 6,
        int $height = 4
    ): array {
        return [
            'title' => $title,
            'chart_type' => $chartType->value,
            'config_json' => [
                'relationship_id' => $relationship?->id,
                'base_column_id' => $baseColumn?->id,
                'value_column_id' => $valueColumn?->id,
                'aggregation' => $aggregation->value,
                'limit' => 10,
                'sort' => 'desc',
                'filters' => [],
            ],
            'width' => $width,
            'height' => $height,
            'reason' => $reason,
        ];
    }

    private function widgetExists(Dashboard $dashboard, array $suggestion): bool
    {
        return DashboardWidget::query()
            ->where('dashboard_id', $dashboard->id)
            ->where('title', $suggestion['title'])
            ->where('chart_type', $suggestion['chart_type'])
            ->exists();
    }

    private function column(Dashboard $dashboard, mixed $columnId): ?DashboardColumn
    {
        if (! $columnId) {
            return null;
        }

        return $dashboard->columns()->find((int) $columnId);
    }

    /**
     * @return array{x: int, y: int}
     */
    private function nextPosition(Dashboard $dashboard): array
    {
        $last = $dashboard->widgets()
            ->orderByDesc('position_y')
            ->orderByDesc('position_x')
            ->first();

        if (! $last) {
            return ['x' => 0, 'y' => 0];
        }

        return $this->advancePosition([
            'x' => $last->position_x,
            'y' => $last->position_y,
        ], $last->width);
    }

    /**
     * @param  array{x: int, y: int}  $position
     * @return array{x: int, y: int}
     */
    private function advancePosition(array $position, int $width): array
    {
        $nextX = $position['x'] + $width;

        if ($nextX >= 12) {
            return ['x' => 0, 'y' => $position['y'] + 1];
        }

        return ['x' => $nextX, 'y' => $position['y']];
    }
}
