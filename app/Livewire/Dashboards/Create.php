<?php

namespace App\Livewire\Dashboards;

use App\Enums\DashboardStatus;
use App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $description = '';

    public bool $canCreate = true;

    public function mount(): void
    {
        $this->canCreate = Auth::user()->sector_id !== null;
    }

    public function save(): void
    {
        $dashboard = $this->createDashboard();

        session()->flash('status', 'Dashboard criado com sucesso.');

        $this->redirectRoute('dashboards.show', ['dashboard' => $dashboard->id], navigate: true);
    }

    public function saveAndImport(): void
    {
        $dashboard = $this->createDashboard();

        session()->flash('status', 'Dashboard criado. Agora você pode importar a planilha.');

        $this->redirectRoute('dashboards.import', ['dashboard' => $dashboard->id], navigate: true);
    }

    private function createDashboard(): Dashboard
    {
        $user = Auth::user();

        abort_unless($user->sector_id !== null, 403, 'Usuário ainda não está vinculado a um setor.');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Informe o nome do dashboard.',
            'name.max' => 'O nome do dashboard deve ser mais curto.',
            'description.max' => 'A descrição deve ser mais curta.',
        ]);

        return Dashboard::query()->create([
            'sector_id' => $user->sector_id,
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'status' => DashboardStatus::Draft,
        ]);
    }

    public function render()
    {
        return view('livewire.dashboards.create')
            ->layout('layouts.app')
            ->title('Criar Dashboard | SEDUC BI');
    }
}
