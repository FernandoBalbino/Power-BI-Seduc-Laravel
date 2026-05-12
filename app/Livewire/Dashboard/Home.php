<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.dashboard.home')
            ->layout('layouts.app')
            ->title('Meus Dashboards | SEDUC BI');
    }
}
