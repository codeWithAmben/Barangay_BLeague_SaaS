<?php

namespace App\Livewire\Players;

use App\Enums\PlayerPosition;
use App\Enums\UserRole;
use App\Models\Player;
use App\Models\Team;
use App\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Players & Rosters')]
class ManagePlayers extends Component
{
    #[Url(as: 'team')]
    public ?int $selectedTeamId = null;

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $team_id = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $jersey_number = '';

    public string $position = 'PG';

    public ?string $birthdate = null;

    public bool $is_import = false;

    public bool $is_suspended = false;

    public ?string $errorBanner = null;

    public function mount(): void
    {
        if ($this->selectedTeamId === null) {
            $this->selectedTeamId = Team::query()->orderBy('name')->value('id');
        }
    }

    public function openCreate(): void
    {
        $this->authorizeCommissioner();
        $this->resetForm();

        $this->team_id = $this->selectedTeamId ?? Team::query()->orderBy('name')->value('id');

        if ($this->team_id === null) {
            $this->errorBanner = 'Please register a team first before adding players.';

            return;
        }

        $team = Team::query()->with('tournament')->findOrFail($this->team_id);

        if ($team->tournament->roster_locked) {
            $this->errorBanner = "The roster for {$team->tournament->name} is currently locked. Unlock it in Tournaments to add players.";

            return;
        }

        $this->showForm = true;
    }

    public function edit(int $playerId): void
    {
        $this->authorizeCommissioner();
        $this->resetValidation();
        $this->errorBanner = null;

        $player = Player::query()->findOrFail($playerId);

        $this->editingId = $player->id;
        $this->team_id = $player->team_id;
        $this->first_name = $player->first_name;
        $this->last_name = $player->last_name;
        $this->jersey_number = $player->jersey_number;
        $this->position = $player->position->value;
        $this->birthdate = $player->birthdate?->format('Y-m-d');
        $this->is_import = $player->is_import;
        $this->is_suspended = $player->is_suspended;
        $this->showForm = true;
    }

    public function save(TenantContext $tenantContext): void
    {
        $this->authorizeCommissioner();

        $validated = $this->validate([
            'team_id' => [
                'required',
                'integer',
                Rule::exists('teams', 'id')->where('tenant_id', $tenantContext->id()),
            ],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'jersey_number' => [
                'required',
                'string',
                'regex:/^(00|[0-9]{1,2})$/',
                Rule::unique('players', 'jersey_number')
                    ->where('team_id', $this->team_id)
                    ->ignore($this->editingId),
            ],
            'position' => ['required', Rule::enum(PlayerPosition::class)],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'is_import' => ['boolean'],
            'is_suspended' => ['boolean'],
        ], [
            'jersey_number.regex' => 'Jersey number must be 00 or a number between 0 and 99.',
            'jersey_number.unique' => 'That jersey number is already assigned to another player on this team.',
        ]);

        $team = Team::query()->with('tournament')->findOrFail($validated['team_id']);

        if ($this->editingId === null) {
            if ($team->tournament->roster_locked) {
                $this->errorBanner = "The roster for {$team->tournament->name} is locked.";

                return;
            }

            Player::create($validated);
            $this->selectedTeamId = $team->id;
            session()->flash('status', 'Player added to roster.');
        } else {
            $player = Player::query()->findOrFail($this->editingId);
            $player->update($validated);
            $this->selectedTeamId = $team->id;
            session()->flash('status', 'Player updated.');
        }

        $this->resetForm();
    }

    public function delete(int $playerId): void
    {
        $this->authorizeCommissioner();

        $player = Player::query()->with('team.tournament')->findOrFail($playerId);

        if ($player->team->tournament->roster_locked) {
            $this->errorBanner = "The roster for {$player->team->tournament->name} is locked. Unlock it before removing players.";

            return;
        }

        $player->delete();
        session()->flash('status', 'Player removed from roster.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function render(): View
    {
        $teams = Team::query()->with('tournament')->orderBy('name')->get();

        $playersQuery = Player::query()
            ->with('team.tournament')
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($this->selectedTeamId !== null) {
            $playersQuery->where('team_id', $this->selectedTeamId);
        }

        return view('livewire.players.manage-players', [
            'teams' => $teams,
            'players' => $playersQuery->get(),
            'positions' => PlayerPosition::cases(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'showForm',
            'editingId',
            'first_name',
            'last_name',
            'jersey_number',
            'birthdate',
            'is_import',
            'is_suspended',
            'errorBanner',
        ]);
        $this->position = PlayerPosition::PointGuard->value;
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
