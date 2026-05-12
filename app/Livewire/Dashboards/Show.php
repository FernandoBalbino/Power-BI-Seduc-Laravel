<?php

namespace App\Livewire\Dashboards;

use App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Dashboard $dashboard;

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->canBeAccessedBy(Auth::user()), 403, 'Você não pode acessar este dashboard.');

        $this->dashboard = $dashboard->load(['sector', 'user']);
    }

    public function render()
    {
        return view('livewire.dashboards.show')
            ->layout('layouts.app')
            ->title($this->dashboard->name.' | SEDUC BI');
    }
}
