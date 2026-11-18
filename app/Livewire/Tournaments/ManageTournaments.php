<?php

namespace App\Livewire\Tournaments;

use App\Enums\TournamentCategory;
use App\Enums\TournamentFormat;
use App\Enums\TournamentStatus;
use App\Enums\UserRole;
use App\Models\Tournament;
use App\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Tournaments')]
class ManageTournaments extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $category = 'open';

    public string $format_type = 'single_round_robin';

    public string $status = 'registration';

    public int $roster_lock_after_game = 2;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public ?string $errorBanner = null;

    public function openCreate(TenantContext $tenantContext): void
    {
        $this->authorizeCommissioner();
        $this->resetForm();

        $tenant = $tenantContext->get();

        if ($tenant !== null && ! $tenant->canCreateTournament()) {
            $max = $tenant->plan_type->maxTournaments();
            $this->errorBanner = "Your {$tenant->plan_type->label()} plan allows up to {$max} tournament. Upgrade to Barangay Pro for unlimited tournaments.";

            return;
        }

        $this->showForm = true;
    }

    public function edit(int $tournamentId): void
    {
        $this->authorizeCommissioner();
        $this->resetValidation();
        $this->errorBanner = null;

        $tournament = Tournament::query()->findOrFail($tournamentId);

        $this->editingId = $tournament->id;
        $this->name = $tournament->name;
        $this->category = $tournament->category->value;
        $this->format_type = $tournament->format_type->value;
        $this->status = $tournament->status->value;
        $this->roster_lock_after_game = $tournament->roster_lock_after_game;
        $this->start_date = $tournament->start_date?->format('Y-m-d');
        $this->end_date = $tournament->end_date?->format('Y-m-d');
        $this->showForm = true;
    }

    public function save(TenantContext $tenantContext): void
    {
        $this->authorizeCommissioner();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::enum(TournamentCategory::class)],
            'format_type' => ['required', Rule::enum(TournamentFormat::class)],
            'status' => ['required', Rule::enum(TournamentStatus::class)],
            'roster_lock_after_game' => ['required', 'integer', 'min:1', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if ($this->editingId === null) {
            $tenant = $tenantContext->get();

            if ($tenant !== null && ! $tenant->canCreateTournament()) {
                $this->errorBanner = 'Trial plan tournament limit reached. Upgrade to Barangay Pro to create more tournaments.';

                return;
            }

            Tournament::create($validated);
            session()->flash('status', 'Tournament created.');
        } else {
            $tournament = Tournament::query()->findOrFail($this->editingId);
            $tournament->update($validated);
            session()->flash('status', 'Tournament updated.');
        }

        $this->resetForm();
    }

    public function toggleRosterLock(int $tournamentId): void
    {
        $this->authorizeCommissioner();

        $tournament = Tournament::query()->findOrFail($tournamentId);
        $tournament->update(['roster_locked' => ! $tournament->roster_locked]);
    }

    public function delete(int $tournamentId): void
    {
        $this->authorizeCommissioner();

        $tournament = Tournament::query()->findOrFail($tournamentId);
        $tournament->delete();
        session()->flash('status', 'Tournament deleted.');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function render(TenantContext $tenantContext): View
    {
        return view('livewire.tournaments.manage-tournaments', [
            'tenant' => $tenantContext->get(),
            'tournaments' => Tournament::query()->withCount(['teams', 'players'])->latest()->get(),
            'categories' => TournamentCategory::cases(),
            'formats' => TournamentFormat::cases(),
            'statuses' => TournamentStatus::cases(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'showForm',
            'editingId',
            'name',
            'start_date',
            'end_date',
            'errorBanner',
        ]);
        $this->category = TournamentCategory::Open->value;
        $this->format_type = TournamentFormat::SingleRoundRobin->value;
        $this->status = TournamentStatus::Registration->value;
        $this->roster_lock_after_game = 2;
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
