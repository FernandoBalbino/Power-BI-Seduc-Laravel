<?php

namespace App\Livewire\Admin\Sectors;

use App\Models\Sector;
use App\Models\User;
use App\Services\SectorRegistrationCodeService;
use Livewire\Component;

class Index extends Component
{
    public function toggleActive(int $sectorId): void
    {
        $sector = Sector::query()->findOrFail($sectorId);

        $sector->update([
            'is_active' => ! $sector->is_active,
        ]);

        session()->flash(
            'status',
            $sector->is_active
                ? 'Setor ativado com sucesso.'
                : 'Setor desativado com sucesso.'
        );
    }

    public function regenerateCode(int $sectorId): void
    {
        $sector = Sector::query()->findOrFail($sectorId);

        $sector->update([
            'registration_code' => app(SectorRegistrationCodeService::class)->generate($sector->id),
        ]);

        session()->flash('status', 'Código de cadastro regenerado com sucesso.');
    }

    public function render()
    {
        $sectors = Sector::query()
            ->withCount('users')
            ->latest()
            ->get();

        $summary = [
            'total' => Sector::query()->count(),
            'active' => Sector::query()->where('is_active', true)->count(),
            'inactive' => Sector::query()->where('is_active', false)->count(),
            'users' => User::query()->whereNotNull('sector_id')->count(),
        ];

        return view('livewire.admin.sectors.index', [
            'sectors' => $sectors,
            'summary' => $summary,
        ])
            ->layout('layouts.app')
            ->title('Setores | SEDUC BI');
    }
}
