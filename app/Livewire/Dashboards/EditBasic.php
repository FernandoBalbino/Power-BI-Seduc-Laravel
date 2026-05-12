<?php

namespace App\Livewire\Dashboards;

use App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditBasic extends Component
{
    public Dashboard $dashboard;

    public string $name = '';

    public string $description = '';

    public function mount(Dashboard $dashboard): void
    {
        abort_unless($dashboard->canBeAccessedBy(Auth::user()), 403, 'Você não pode editar este dashboard.');

        $this->dashboard = $dashboard;
        $this->name = $dashboard->name;
        $this->description = $dashboard->description ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Informe o nome do dashboard.',
            'name.max' => 'O nome do dashboard deve ser mais curto.',
            'description.max' => 'A descrição deve ser mais curta.',
        ]);

        $this->dashboard->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
        ]);

        session()->flash('status', 'Informações básicas atualizadas com sucesso.');

        $this->redirectRoute('dashboards.show', ['dashboard' => $this->dashboard->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.dashboards.edit-basic')
            ->layout('layouts.app')
            ->title('Editar Informações | SEDUC BI');
    }
}
