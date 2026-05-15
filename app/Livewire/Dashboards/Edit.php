<?php

namespace App\Livewire\Dashboards;

use App\Enums\DashboardRelationshipAggregation;
use App\Enums\DashboardWidgetChartType;
use App\Models\Dashboard;
use App\Models\DashboardColumn;
use App\Models\DashboardWidget;
use App\Services\ChartSuggestionService;
use App\Services\DashboardQueryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Edit extends Component
{
    public Dashboard $dashboard;

    public string $manualTitle = '';

    public string $manualChartType = 'bar';

    public ?int $manualGroupingColumnId = null;

    public ?int $manualValueColumnId = null;

    public string $manualAggregation = 'sum';

    public string $manualSort = 'desc';

    public int $manualLimit = 10;

    public ?int $manualFilterColumnId = null;

    public string $manualFilterValue = '';

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->canBeAccessedBy(Auth::user()), 403, 'Você não pode editar este dashboard.');

        $this->dashboard = $dashboard->load(['sector', 'user', 'columns', 'widgets']);
        $this->manualGroupingColumnId = $this->dashboard->dimensionalColumns()->first()?->id;
        $this->manualValueColumnId = $this->dashboard->metricColumns()->first()?->id;
        $this->manualAggregation = $this->manualValueColumnId
            ? DashboardRelationshipAggregation::Sum->value
            : DashboardRelationshipAggregation::Count->value;
        $this->manualTitle = 'Novo gráfico';
    }

    public function updatedManualAggregation(string $aggregation): void
    {
        if ($aggregation === DashboardRelationshipAggregation::Count->value) {
            $this->manualValueColumnId = null;

            return;
        }

        $this->manualValueColumnId ??= $this->dashboard->metricColumns()->first()?->id;
    }

    public function updatedManualFilterColumnId(): void
    {
        $this->manualFilterValue = '';
    }

    public function generateAutomaticWidgets(): void
    {
        $created = app(ChartSuggestionService::class)->createAutomaticWidgets($this->dashboard);
        $this->dashboard->refresh();

        if ($created === []) {
            $this->addError('automaticWidgets', 'Nenhum gráfico novo foi encontrado. Verifique os relacionamentos ou widgets já criados.');

            return;
        }

        session()->flash('status', count($created).' widget(s) criado(s) com sucesso.');
    }

    public function saveManualWidget(): void
    {
        if ($this->manualAggregation !== DashboardRelationshipAggregation::Count->value
            && ! $this->manualValueColumnId
            && ! $this->dashboard->metricColumns()->exists()) {
            $this->manualAggregation = DashboardRelationshipAggregation::Count->value;
        }

        $this->validate([
            'manualTitle' => ['required', 'string', 'max:120'],
            'manualChartType' => ['required', 'in:card,bar,line,pie,donut,area,table'],
            'manualGroupingColumnId' => ['nullable', 'integer'],
            'manualValueColumnId' => ['nullable', 'integer'],
            'manualAggregation' => ['required', 'in:sum,avg,count,min,max'],
            'manualSort' => ['required', 'in:asc,desc'],
            'manualLimit' => ['required', 'integer', 'min:1', 'max:50'],
            'manualFilterColumnId' => ['nullable', 'integer'],
            'manualFilterValue' => ['nullable', 'string', 'max:255'],
        ], [
            'manualTitle.required' => 'Informe um título para o gráfico.',
            'manualLimit.max' => 'Use no máximo 50 resultados.',
        ]);

        try {
            app(ChartSuggestionService::class)->createManualWidget(
                $this->dashboard,
                $this->manualTitle,
                $this->manualChartType,
                [
                    'base_column_id' => $this->manualChartType === DashboardWidgetChartType::Card->value ? null : $this->manualGroupingColumnId,
                    'value_column_id' => $this->manualAggregation === DashboardRelationshipAggregation::Count->value ? null : $this->manualValueColumnId,
                    'aggregation' => $this->manualAggregation,
                    'limit' => $this->manualLimit,
                    'sort' => $this->manualSort,
                    'filters' => $this->manualFilters(),
                ],
                $this->manualChartType === DashboardWidgetChartType::Card->value ? 3 : 6,
                $this->manualChartType === DashboardWidgetChartType::Card->value ? 2 : 4
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->dashboard->refresh();
        $this->manualTitle = 'Novo gráfico';

        session()->flash('status', 'Widget criado com sucesso.');
    }

    public function removeWidget(int $widgetId): void
    {
        DashboardWidget::query()
            ->where('dashboard_id', $this->dashboard->id)
            ->findOrFail($widgetId)
            ->delete();

        $this->dashboard->refresh();

        session()->flash('status', 'Widget removido com sucesso.');
    }

    public function getChartTypeOptionsProperty(): array
    {
        return DashboardWidgetChartType::options();
    }

    public function getAggregationOptionsProperty(): array
    {
        return DashboardRelationshipAggregation::options();
    }

    public function getIsCardWidgetProperty(): bool
    {
        return $this->manualChartType === DashboardWidgetChartType::Card->value;
    }

    public function getUsesRecordCountProperty(): bool
    {
        return $this->manualAggregation === DashboardRelationshipAggregation::Count->value;
    }

    public function getManualFilterValuesProperty(): array
    {
        $column = $this->manualFilterColumn();

        if (! $column) {
            return [];
        }

        return $this->dashboard->rows()
            ->get()
            ->map(fn ($row) => $row->data_json[$column->normalized_name] ?? null)
            ->filter(fn (mixed $value) => $value !== null && $value !== '')
            ->map(fn (mixed $value) => is_bool($value) ? ($value ? 'Sim' : 'Não') : (string) $value)
            ->unique()
            ->sort()
            ->take(50)
            ->values()
            ->all();
    }

    public function render()
    {
        $this->dashboard->load(['columns', 'widgets']);
        $queryService = app(DashboardQueryService::class);

        return view('livewire.dashboards.edit')
            ->with([
                'columns' => $this->dashboard->columns,
                'groupingColumns' => $this->dashboard->columns,
                'valueColumns' => $this->dashboard->metricColumns()->get(),
                'relationships' => $this->dashboard->validRelationships()->get(),
                'widgets' => $this->dashboard->widgets,
                'widgetData' => $this->dashboard->widgets
                    ->mapWithKeys(fn (DashboardWidget $widget) => [$widget->id => $queryService->dataForWidget($widget)])
                    ->all(),
                'suggestions' => app(ChartSuggestionService::class)->suggest($this->dashboard),
            ])
            ->layout('layouts.app')
            ->title('Editar Dashboard | SEDUC BI');
    }

    private function manualFilterColumn(): ?DashboardColumn
    {
        if (! $this->manualFilterColumnId) {
            return null;
        }

        return $this->dashboard->columns()->find($this->manualFilterColumnId);
    }

    /**
     * @return array<int, array{column_id: int, operator: string, value: string}>
     */
    private function manualFilters(): array
    {
        if (! $this->manualFilterColumnId || $this->manualFilterValue === '') {
            return [];
        }

        return [[
            'column_id' => $this->manualFilterColumnId,
            'operator' => 'equals',
            'value' => $this->manualFilterValue,
        ]];
    }
}
