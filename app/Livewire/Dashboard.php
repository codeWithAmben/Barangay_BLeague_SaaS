<?php

namespace App\Livewire;

use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use App\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render(TenantContext $tenantContext): View
    {
        $tenant = $tenantContext->get();

        $tournaments = Tournament::query()
            ->withCount(['teams', 'players'])
            ->latest()
            ->get();

        return view('livewire.dashboard', [
            'tenant' => $tenant,
            'tournaments' => $tournaments,
            'tournamentCount' => $tournaments->count(),
            'teamCount' => Team::query()->count(),
            'playerCount' => Player::query()->count(),
        ]);
    }
}
