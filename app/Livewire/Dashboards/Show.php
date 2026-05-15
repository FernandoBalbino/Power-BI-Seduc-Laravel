<?php

namespace App\Livewire\Dashboards;

use App\Models\Dashboard;
use App\Models\DashboardWidget;
use App\Services\DashboardQueryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Dashboard $dashboard;

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->canBeAccessedBy(Auth::user()), 403, 'Você não pode acessar este dashboard.');

        $this->dashboard = $dashboard->load(['sector', 'user', 'widgets']);
    }

    public function render()
    {
        $this->dashboard->load(['widgets']);
        $queryService = app(DashboardQueryService::class);

        return view('livewire.dashboards.show')
            ->with([
                'widgets' => $this->dashboard->widgets,
                'widgetData' => $this->dashboard->widgets
                    ->mapWithKeys(fn (DashboardWidget $widget) => [$widget->id => $queryService->dataForWidget($widget)])
                    ->all(),
            ])
            ->layout('layouts.app')
            ->title($this->dashboard->name.' | SEDUC BI');
    }
}
