<?php

namespace App\Livewire\Dashboards;

use App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    public Dashboard $dashboard;

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->canBeAccessedBy(Auth::user()), 403, 'Você não pode editar este dashboard.');

        $this->dashboard = $dashboard->load(['sector', 'user']);
    }

    public function render()
    {
        return view('livewire.dashboards.edit')
            ->layout('layouts.app')
            ->title('Editar Dashboard | SEDUC BI');
    }
}
