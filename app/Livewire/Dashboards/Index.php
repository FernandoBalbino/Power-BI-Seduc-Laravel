<?php

namespace App\Livewire\Dashboards;

use App\Enums\DashboardStatus;
use App\Models\Dashboard;
use App\Models\Sector;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public ?int $selectedSectorId = null;

    public function selectSector(int $sectorId): void
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $this->selectedSectorId = $sectorId;
    }

    public function clearSectorSelection(): void
    {
        $this->selectedSectorId = null;
    }

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
        $isAdmin = $user->isAdmin();
        $selectedSector = null;
        $sectorCards = collect();

        $baseQuery = Dashboard::query()->visibleTo($user);

        if ($isAdmin) {
            $sectorCards = Sector::query()
                ->whereHas('dashboards', fn ($query) => $query->where('status', DashboardStatus::Draft->value))
                ->withCount([
                    'dashboards',
                    'dashboards as draft_dashboards_count' => fn ($query) => $query->where('status', DashboardStatus::Draft->value),
                    'dashboards as ready_dashboards_count' => fn ($query) => $query->where('status', DashboardStatus::Ready->value),
                    'users',
                ])
                ->withMax('dashboards', 'updated_at')
                ->orderBy('name')
                ->get();

            if ($this->selectedSectorId) {
                $selectedSector = Sector::query()->find($this->selectedSectorId);

                if (! $selectedSector) {
                    $this->selectedSectorId = null;
                } else {
                    $baseQuery->where('sector_id', $selectedSector->id);
                }
            }
        }

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
            'isAdmin' => $isAdmin,
            'sectorCards' => $sectorCards,
            'selectedSector' => $selectedSector,
        ])
            ->layout('layouts.app')
            ->title('Meus Dashboards | SEDUC BI');
    }
}
