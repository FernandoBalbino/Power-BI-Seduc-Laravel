<?php

namespace App\Livewire\Dashboards;

use App\Enums\DashboardStatus;
use App\Models\Dashboard;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public function deleteDashboard(int $dashboardId): void
    {
        $dashboard = Dashboard::query()
            ->visibleTo(Auth::user())
            ->findOrFail($dashboardId);

        $dashboard->delete();

        session()->flash('status', 'Dashboard apagado com sucesso.');
    }

    public function render()
    {
        $user = Auth::user();
        $baseQuery = Dashboard::query()->visibleTo($user);

        $dashboards = (clone $baseQuery)
            ->with(['sector', 'user'])
            ->latest('updated_at')
            ->get();

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', DashboardStatus::Draft->value)->count(),
            'ready' => (clone $baseQuery)->where('status', DashboardStatus::Ready->value)->count(),
            'records' => 0,
        ];

        return view('livewire.dashboards.index', [
            'dashboards' => $dashboards,
            'summary' => $summary,
            'canCreate' => $user->sector_id !== null,
        ])
            ->layout('layouts.app')
            ->title('Meus Dashboards | SEDUC BI');
    }
}
