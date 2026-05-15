<?php

namespace App\Livewire\Dashboards;

use App\Enums\DashboardRelationshipAggregation;
use App\Enums\DashboardRelationshipType;
use App\Models\Dashboard;
use App\Models\DashboardColumn;
use App\Models\DashboardRelationship;
use App\Services\RelationshipSuggestionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Relationships extends Component
{
    public Dashboard $dashboard;

    public string $mode = 'automatic';

    public array $selectedSuggestions = [];

    public ?int $manualBaseColumnId = null;

    public array $manualRelatedColumnIds = [];

    public array $manualAggregations = [];

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->canBeAccessedBy(Auth::user()), 403, 'Você não pode relacionar colunas deste dashboard.');

        $this->dashboard = $dashboard->load(['sector', 'columns']);
        $queryMode = request()->query('modo');

        if (in_array($queryMode, ['automatic', 'manual'], true)) {
            $this->mode = $queryMode;
        }

        $this->manualBaseColumnId = app(RelationshipSuggestionService::class)
            ->baseColumns($this->dashboard)
            ->first()?->id;
    }

    public function setMode(string $mode): void
    {
        if (! in_array($mode, ['automatic', 'manual'], true)) {
            return;
        }

        $this->mode = $mode;
        $this->resetErrorBag();
    }

    public function updatedManualBaseColumnId(): void
    {
        $this->manualRelatedColumnIds = array_values(array_filter(
            $this->manualRelatedColumnIds,
            fn (string|int $columnId) => (string) $columnId !== (string) $this->manualBaseColumnId
        ));
    }

    public function acceptAllSuggestions(): void
    {
        foreach ($this->currentSuggestions() as $suggestion) {
            $this->selectedSuggestions[$suggestion['key']] = true;
        }

        $this->saveAutomaticRelationships();
    }

    public function saveAutomaticRelationships(): void
    {
        $service = app(RelationshipSuggestionService::class);
        $columns = $this->dashboard->columns()->get()->keyBy('id');
        $created = 0;

        foreach ($this->currentSuggestions() as $suggestion) {
            if (! (bool) ($this->selectedSuggestions[$suggestion['key']] ?? false)) {
                continue;
            }

            $baseColumn = $columns->get($suggestion['base_column_id']);
            $relatedColumn = $suggestion['related_column_id']
                ? $columns->get($suggestion['related_column_id'])
                : null;

            if (! $baseColumn) {
                continue;
            }

            try {
                $service->createRelationship(
                    $this->dashboard,
                    $baseColumn,
                    $relatedColumn,
                    $suggestion['aggregation'],
                    DashboardRelationshipType::Auto
                );
                $created++;
            } catch (ValidationException $exception) {
                $this->addError('automaticRelationships', collect($exception->errors())->flatten()->first());

                return;
            }
        }

        if ($created === 0) {
            $this->addError('automaticRelationships', 'Selecione ao menos uma sugestão para salvar.');

            return;
        }

        $this->dashboard->refresh();
        $this->selectedSuggestions = [];

        session()->flash('status', $created.' relacionamento(s) criado(s) com sucesso.');
    }

    public function saveManualRelationships(): void
    {
        $service = app(RelationshipSuggestionService::class);
        $columns = $this->dashboard->columns()->get()->keyBy('id');
        $baseColumn = $columns->get($this->manualBaseColumnId);

        if (! $baseColumn) {
            $this->addError('manualRelationships', 'Escolha uma coluna base.');

            return;
        }

        $selectedRelatedColumns = collect($this->manualRelatedColumnIds)
            ->map(fn (string|int $columnId) => (string) $columnId)
            ->unique()
            ->values();

        if ($selectedRelatedColumns->isEmpty()) {
            $this->addError('manualRelationships', 'Escolha pelo menos uma coluna relacionada.');

            return;
        }

        $created = 0;

        foreach ($selectedRelatedColumns as $relatedColumnId) {
            $relatedColumn = $relatedColumnId === 'records_count' ? null : $columns->get((int) $relatedColumnId);
            $aggregation = $relatedColumnId === 'records_count'
                ? DashboardRelationshipAggregation::Count->value
                : ($this->manualAggregations[$relatedColumnId] ?? DashboardRelationshipAggregation::Sum->value);

            try {
                $service->createRelationship(
                    $this->dashboard,
                    $baseColumn,
                    $relatedColumn,
                    $aggregation,
                    DashboardRelationshipType::Manual
                );
                $created++;
            } catch (ValidationException $exception) {
                $this->addError('manualRelationships', collect($exception->errors())->flatten()->first());

                return;
            }
        }

        $this->dashboard->refresh();
        $this->manualRelatedColumnIds = [];
        $this->manualAggregations = [];

        session()->flash('status', $created.' relacionamento(s) manual(is) criado(s) com sucesso.');
    }

    public function removeRelationship(int $relationshipId): void
    {
        $relationship = DashboardRelationship::query()
            ->where('dashboard_id', $this->dashboard->id)
            ->findOrFail($relationshipId);

        $relationship->delete();
        $this->dashboard->refresh();

        session()->flash('status', 'Relacionamento removido com sucesso.');
    }

    public function getAggregationOptionsProperty(): array
    {
        return DashboardRelationshipAggregation::options();
    }

    public function render()
    {
        $service = app(RelationshipSuggestionService::class);
        $this->dashboard->load(['sector', 'columns', 'validRelationships.baseColumn', 'validRelationships.relatedColumn']);

        $suggestions = $this->currentSuggestions();

        foreach ($suggestions as $suggestion) {
            $this->selectedSuggestions[$suggestion['key']] ??= true;
        }

        $columns = $this->dashboard->columns;
        $relationships = $this->dashboard->validRelationships;
        $relatedColumnIds = $relationships
            ->flatMap(fn (DashboardRelationship $relationship) => [
                $relationship->base_column_id,
                $relationship->related_column_id,
            ])
            ->filter()
            ->unique();

        return view('livewire.dashboards.relationships', [
            'suggestions' => $suggestions,
            'baseColumns' => $service->baseColumns($this->dashboard),
            'relatedColumns' => $service->relatedColumns($this->dashboard),
            'relationships' => $relationships,
            'summary' => [
                'columns' => $columns->count(),
                'relationships' => $relationships->count(),
                'suggestions' => count($suggestions),
                'without_relationship' => $columns->reject(fn (DashboardColumn $column) => $relatedColumnIds->contains($column->id))->count(),
            ],
            'fieldCounts' => $columns
                ->groupBy(fn (DashboardColumn $column) => $column->type->label())
                ->map->count()
                ->sortKeys(),
        ])
            ->layout('layouts.app')
            ->title('Relacionar Colunas | SEDUC BI');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function currentSuggestions(): array
    {
        return app(RelationshipSuggestionService::class)->suggest($this->dashboard);
    }
}
