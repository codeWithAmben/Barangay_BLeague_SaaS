<?php

namespace App\Livewire\Teams;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\Tournament;
use App\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Teams & Puroks')]
class ManageTeams extends Component
{
    #[Url(as: 'tournament')]
    public ?int $selectedTournamentId = null;

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $tournament_id = null;

    public string $name = '';

    public string $purok_zone = '';

    public string $coach_name = '';

    public string $manager_name = '';

    public string $primary_color = '#ea580c';

    public string $secondary_color = '#1e293b';

    public ?string $errorBanner = null;

    public function mount(): void
    {
        if ($this->selectedTournamentId === null) {
            $this->selectedTournamentId = Tournament::query()->latest()->value('id');
        }
    }

    public function openCreate(TenantContext $tenantContext): void
    {
        $this->authorizeCommissioner();
        $this->resetForm();

        $this->tournament_id = $this->selectedTournamentId ?? Tournament::query()->latest()->value('id');

        if ($this->tournament_id === null) {
            $this->errorBanner = 'Please create a tournament first before adding teams.';

            return;
        }

        $tournament = Tournament::query()->findOrFail($this->tournament_id);
        $tenant = $tenantContext->get();

        if ($tenant !== null && ! $tenant->canAddTeam($tournament)) {
            $max = $tenant->plan_type->maxTeamsPerTournament();
            $this->errorBanner = "Your {$tenant->plan_type->label()} plan allows up to {$max} teams per tournament. Upgrade to Barangay Pro for unlimited teams.";

            return;
        }

        $this->showForm = true;
    }

    public function edit(int $teamId): void
    {
        $this->authorizeCommissioner();
        $this->resetValidation();
        $this->errorBanner = null;

        $team = Team::query()->findOrFail($teamId);

        $this->editingId = $team->id;
        $this->tournament_id = $team->tournament_id;
        $this->name = $team->name;
        $this->purok_zone = $team->purok_zone ?? '';
        $this->coach_name = $team->coach_name ?? '';
        $this->manager_name = $team->manager_name ?? '';
        $this->primary_color = $team->primary_color ?? '#ea580c';
        $this->secondary_color = $team->secondary_color ?? '#1e293b';
        $this->showForm = true;
    }

    public function save(TenantContext $tenantContext): void
    {
        $this->authorizeCommissioner();

        $validated = $this->validate([
            'tournament_id' => [
                'required',
                'integer',
                Rule::exists('tournaments', 'id')->where('tenant_id', $tenantContext->id()),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('teams', 'name')
                    ->where('tournament_id', $this->tournament_id)
                    ->ignore($this->editingId),
            ],
            'purok_zone' => ['nullable', 'string', 'max:80'],
            'coach_name' => ['nullable', 'string', 'max:100'],
            'manager_name' => ['nullable', 'string', 'max:100'],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tournament = Tournament::query()->findOrFail($validated['tournament_id']);

        if ($this->editingId === null) {
            $tenant = $tenantContext->get();

            if ($tenant !== null && ! $tenant->canAddTeam($tournament)) {
                $this->errorBanner = 'Team limit reached for your current plan.';

                return;
            }

            Team::create($validated);
            $this->selectedTournamentId = $tournament->id;
            session()->flash('status', 'Team registered.');
        } else {
            $team = Team::query()->findOrFail($this->editingId);
            $team->update($validated);
            $this->selectedTournamentId = $tournament->id;
            session()->flash('status', 'Team updated.');
        }

        $this->resetForm();
    }

    public function delete(int $teamId): void
    {
        $this->authorizeCommissioner();

        $team = Team::query()->findOrFail($teamId);
        $team->delete();
        session()->flash('status', 'Team deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function render(TenantContext $tenantContext): View
    {
        $tournaments = Tournament::query()->orderBy('name')->get();

        $teamsQuery = Team::query()
            ->with('tournament')
            ->withCount('players')
            ->orderBy('name');

        if ($this->selectedTournamentId !== null) {
            $teamsQuery->where('tournament_id', $this->selectedTournamentId);
        }

        return view('livewire.teams.manage-teams', [
            'tenant' => $tenantContext->get(),
            'tournaments' => $tournaments,
            'teams' => $teamsQuery->get(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'showForm',
            'editingId',
            'name',
            'purok_zone',
            'coach_name',
            'manager_name',
            'errorBanner',
        ]);
        $this->primary_color = '#ea580c';
        $this->secondary_color = '#1e293b';
        $this->resetValidation();
    }

    private function authorizeCommissioner(): void
    {
        $user = auth()->user();

        if ($user === null || ! in_array($user->role, [UserRole::Commissioner, UserRole::SuperAdmin], true)) {
            abort(403);
        }
    }
}
