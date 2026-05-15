<?php

namespace App\Services;

use App\Enums\DashboardColumnType;
use App\Enums\DashboardRelationshipAggregation;
use App\Enums\DashboardRelationshipType;
use App\Models\Dashboard;
use App\Models\DashboardColumn;
use App\Models\DashboardRelationship;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RelationshipSuggestionService
{
    /**
     * @return array<int, array{
     *     key: string,
     *     base_column_id: int,
     *     related_column_id: int|null,
     *     base_name: string,
     *     related_name: string,
     *     aggregation: string,
     *     aggregation_label: string,
     *     reason: string,
     *     chart_types: array<int, string>
     * }>
     */
    public function suggest(Dashboard $dashboard): array
    {
        $dashboard->loadMissing(['columns', 'relationships']);
        $suggestions = [];
        $baseColumns = $this->baseColumns($dashboard)
            ->reject(fn (DashboardColumn $column) => $column->type === DashboardColumnType::Identifier);
        $metricColumns = $this->relatedColumns($dashboard)
            ->filter(fn (DashboardColumn $column) => $column->isMetric());

        foreach ($baseColumns as $baseColumn) {
            if ($this->shouldSuggestCount($baseColumn)) {
                $this->pushSuggestion(
                    $suggestions,
                    $dashboard,
                    $baseColumn,
                    null,
                    DashboardRelationshipAggregation::Count,
                    'Conta quantos registros existem para cada '.$baseColumn->displayName().'.'
                );
            }

            foreach ($metricColumns as $metricColumn) {
                $aggregation = $this->suggestAggregation($baseColumn, $metricColumn);

                if (! $aggregation) {
                    continue;
                }

                $this->pushSuggestion(
                    $suggestions,
                    $dashboard,
                    $baseColumn,
                    $metricColumn,
                    $aggregation,
                    $this->suggestionReason($baseColumn, $metricColumn, $aggregation)
                );
            }
        }

        return array_values($suggestions);
    }

    /**
     * @return Collection<int, DashboardColumn>
     */
    public function baseColumns(Dashboard $dashboard): Collection
    {
        return $dashboard->columns()
            ->whereIn('type', [
                DashboardColumnType::ShortText->value,
                DashboardColumnType::Category->value,
                DashboardColumnType::Date->value,
                DashboardColumnType::Boolean->value,
                DashboardColumnType::Identifier->value,
            ])
            ->get();
    }

    /**
     * @return Collection<int, DashboardColumn>
     */
    public function relatedColumns(Dashboard $dashboard): Collection
    {
        return $dashboard->columns()
            ->where('type', '!=', DashboardColumnType::Ignore->value)
            ->get();
    }

    public function createRelationship(
        Dashboard $dashboard,
        DashboardColumn $baseColumn,
        ?DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation|string $aggregation,
        DashboardRelationshipType|string $relationshipType
    ): DashboardRelationship {
        $aggregation = $aggregation instanceof DashboardRelationshipAggregation
            ? $aggregation
            : DashboardRelationshipAggregation::from($aggregation);
        $relationshipType = $relationshipType instanceof DashboardRelationshipType
            ? $relationshipType
            : DashboardRelationshipType::from($relationshipType);

        $this->validateRelationship($dashboard, $baseColumn, $relatedColumn, $aggregation);

        return DashboardRelationship::query()->create([
            'dashboard_id' => $dashboard->id,
            'base_column_id' => $baseColumn->id,
            'related_column_id' => $relatedColumn?->id,
            'aggregation' => $aggregation,
            'relationship_type' => $relationshipType,
        ]);
    }

    public function validateRelationship(
        Dashboard $dashboard,
        DashboardColumn $baseColumn,
        ?DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation $aggregation
    ): void {
        if ($baseColumn->dashboard_id !== $dashboard->id || ($relatedColumn && $relatedColumn->dashboard_id !== $dashboard->id)) {
            throw ValidationException::withMessages([
                'relationship' => 'Use apenas colunas deste dashboard.',
            ]);
        }

        if ($baseColumn->type === DashboardColumnType::Ignore || $relatedColumn?->type === DashboardColumnType::Ignore) {
            throw ValidationException::withMessages([
                'relationship' => 'Colunas ignoradas não podem entrar nos relacionamentos.',
            ]);
        }

        if ($relatedColumn && $baseColumn->id === $relatedColumn->id) {
            throw ValidationException::withMessages([
                'relationship' => 'A coluna base não pode se relacionar com ela mesma.',
            ]);
        }

        if ($aggregation !== DashboardRelationshipAggregation::Count && ! $relatedColumn) {
            throw ValidationException::withMessages([
                'relationship' => 'Escolha uma coluna relacionada para este cálculo.',
            ]);
        }

        if ($this->relationshipExists($dashboard, $baseColumn, $relatedColumn, $aggregation)) {
            throw ValidationException::withMessages([
                'relationship' => 'Este relacionamento já existe.',
            ]);
        }

        if (! $this->aggregationIsAllowed($relatedColumn, $aggregation)) {
            throw ValidationException::withMessages([
                'relationship' => $this->invalidAggregationMessage($aggregation),
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    public function chartTypesForColumns(
        DashboardColumn $baseColumn,
        ?DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation|string $aggregation
    ): array {
        $aggregation = $aggregation instanceof DashboardRelationshipAggregation
            ? $aggregation
            : DashboardRelationshipAggregation::from($aggregation);

        if ($aggregation === DashboardRelationshipAggregation::Count) {
            return ['Barras', 'Rosca', 'Indicador'];
        }

        if ($baseColumn->type === DashboardColumnType::Date) {
            return ['Linha', 'Barras'];
        }

        if ($relatedColumn?->type === DashboardColumnType::Percentage) {
            return ['Barras', 'Linha', 'Indicador'];
        }

        if ($relatedColumn?->isMetric()) {
            return ['Barras', 'Indicador', 'Tabela'];
        }

        return ['Tabela'];
    }

    private function shouldSuggestCount(DashboardColumn $baseColumn): bool
    {
        return in_array($baseColumn->type, [
            DashboardColumnType::ShortText,
            DashboardColumnType::Category,
            DashboardColumnType::Boolean,
        ], true);
    }

    private function suggestAggregation(DashboardColumn $baseColumn, DashboardColumn $metricColumn): ?DashboardRelationshipAggregation
    {
        if ($metricColumn->type === DashboardColumnType::Percentage) {
            return DashboardRelationshipAggregation::Avg;
        }

        if ($baseColumn->type === DashboardColumnType::Date && $metricColumn->isMetric()) {
            return DashboardRelationshipAggregation::Sum;
        }

        if (in_array($baseColumn->type, [
            DashboardColumnType::ShortText,
            DashboardColumnType::Category,
            DashboardColumnType::Boolean,
        ], true) && $metricColumn->isMetric()) {
            return DashboardRelationshipAggregation::Sum;
        }

        return null;
    }

    private function suggestionReason(
        DashboardColumn $baseColumn,
        DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation $aggregation
    ): string {
        if ($baseColumn->type === DashboardColumnType::Date) {
            return 'Agrupa valores por período para acompanhar evolução ao longo do tempo.';
        }

        if ($aggregation === DashboardRelationshipAggregation::Avg) {
            return 'Calcula a média de porcentagens para cada '.$baseColumn->displayName().'.';
        }

        if ($relatedColumn->type === DashboardColumnType::Money) {
            return 'Soma valores em dinheiro agrupados por '.$baseColumn->displayName().'.';
        }

        return 'Agrupa números por '.$baseColumn->displayName().' para comparar resultados.';
    }

    /**
     * @param  array<string, array<string, mixed>>  $suggestions
     */
    private function pushSuggestion(
        array &$suggestions,
        Dashboard $dashboard,
        DashboardColumn $baseColumn,
        ?DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation $aggregation,
        string $reason
    ): void {
        if ($this->relationshipExists($dashboard, $baseColumn, $relatedColumn, $aggregation)) {
            return;
        }

        $key = $this->relationshipKey($baseColumn, $relatedColumn, $aggregation);

        $suggestions[$key] = [
            'key' => $key,
            'base_column_id' => $baseColumn->id,
            'related_column_id' => $relatedColumn?->id,
            'base_name' => $baseColumn->displayName(),
            'related_name' => $relatedColumn?->displayName() ?? 'Quantidade de registros',
            'aggregation' => $aggregation->value,
            'aggregation_label' => $aggregation->label(),
            'reason' => $reason,
            'chart_types' => $this->chartTypesForColumns($baseColumn, $relatedColumn, $aggregation),
        ];
    }

    private function relationshipKey(
        DashboardColumn $baseColumn,
        ?DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation $aggregation
    ): string {
        return 'rel_'.$baseColumn->id.'_'.($relatedColumn?->id ?? 'registros').'_'.$aggregation->value;
    }

    private function relationshipExists(
        Dashboard $dashboard,
        DashboardColumn $baseColumn,
        ?DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation $aggregation
    ): bool {
        return DashboardRelationship::query()
            ->where('dashboard_id', $dashboard->id)
            ->where('base_column_id', $baseColumn->id)
            ->when(
                $relatedColumn,
                fn ($query) => $query->where('related_column_id', $relatedColumn->id),
                fn ($query) => $query->whereNull('related_column_id')
            )
            ->where('aggregation', $aggregation->value)
            ->exists();
    }

    private function aggregationIsAllowed(
        ?DashboardColumn $relatedColumn,
        DashboardRelationshipAggregation $aggregation
    ): bool {
        if ($aggregation === DashboardRelationshipAggregation::Count) {
            return true;
        }

        if (! $relatedColumn) {
            return false;
        }

        return match ($aggregation) {
            DashboardRelationshipAggregation::Sum,
            DashboardRelationshipAggregation::Avg => $relatedColumn->isMetric(),
            DashboardRelationshipAggregation::Min,
            DashboardRelationshipAggregation::Max => $relatedColumn->isMetric()
                || $relatedColumn->type === DashboardColumnType::Date,
            DashboardRelationshipAggregation::Count => true,
        };
    }

    private function invalidAggregationMessage(DashboardRelationshipAggregation $aggregation): string
    {
        return match ($aggregation) {
            DashboardRelationshipAggregation::Sum => 'Soma só pode ser usada com Número, Dinheiro ou Porcentagem.',
            DashboardRelationshipAggregation::Avg => 'Média só pode ser usada com Número, Dinheiro ou Porcentagem.',
            DashboardRelationshipAggregation::Min,
            DashboardRelationshipAggregation::Max => 'Mínimo e máximo precisam de Número, Dinheiro, Porcentagem ou Data.',
            DashboardRelationshipAggregation::Count => 'Contagem pode ser usada com colunas úteis do dashboard.',
        };
    }
}
