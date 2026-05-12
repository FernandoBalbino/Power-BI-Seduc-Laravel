<?php

namespace App\Livewire\Admin\Sectors;

use App\Models\Sector;
use App\Models\User;
use Livewire\Component;

class Users extends Component
{
    public Sector $sector;

    public function mount(Sector $sector): void
    {
        $this->sector = $sector;
    }

    public function render()
    {
        $users = User::query()
            ->where('sector_id', $this->sector->id)
            ->latest()
            ->get();

        return view('livewire.admin.sectors.users', [
            'users' => $users,
        ])
            ->layout('layouts.app')
            ->title('Usuários do Setor | SEDUC BI');
    }
}
