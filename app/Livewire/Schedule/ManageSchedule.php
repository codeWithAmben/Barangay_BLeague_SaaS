<?php

namespace App\Livewire\Schedule;

use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Models\Game;
use App\Models\Tournament;
use App\Services\RoundRobinScheduler;
use App\Services\StandingsCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Schedule & Standings')]
class ManageSchedule extends Component
{
    #[Url(as: 'tournament')]
    public ?int $selectedTournamentId = null;

    public string $venue = 'Barangay Covered Court';

    public string $start_date = '';

    public string $first_tipoff = '18:00';

    public int $games_per_day = 2;

    public int $minutes_between_games = 90;

    public int $days_between_gamedays = 1;

    public bool $replace_scheduled = true;

    public bool $showGenerator = false;

    public ?int $editingGameId = null;

    public int $homeScore = 0;

    public int $awayScore = 0;

    public string $gameStatus = 'finished';

    public ?string $errorBanner = null;

    public function mount(): void
    {
        $this->start_date = now()->addDay()->format('Y-m-d');

        if ($this->selectedTournamentId === null) {
            $this->selectedTournamentId = Tournament::query()->latest()->value('id');
        }
    }

    public function toggleGenerator(): void
    {
        $this->authorizeCommissioner();
        $this->errorBanner = null;
        $this->showGenerator = ! $this->showGenerator;
    }

    public function generateSchedule(RoundRobinScheduler $scheduler): void
    {
        $this->authorizeCommissioner();
        $this->errorBanner = null;

        if ($this->selectedTournamentId === null) {
            $this->errorBanner = 'Please select or create a tournament first.';

            return;
        }

        $validated = $this->validate([
            'venue' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'first_tipoff' => ['required', 'date_format:H:i'],
            'games_per_day' => ['required', 'integer', 'min:1', 'max:12'],
            'minutes_between_games' => ['required', 'integer', 'min:30', 'max:240'],
            'days_between_gamedays' => ['required', 'integer', 'min:1', 'max:14'],
            'replace_scheduled' => ['boolean'],
        ]);

        $tournament = Tournament::query()->findOrFail($this->selectedTournamentId);

        try {
            $games = $scheduler->generateForTournament($tournament, $validated);
        } catch (InvalidArgumentException $exception) {
            $this->errorBanner = $exception->getMessage();

            return;
        }

        $roundCount = $games->max('round_number') ?? 0;
        $this->showGenerator = false;
        session()->flash('status', "Generated {$games->count()} games across {$roundCount} rounds.");
    }

    public function openScoreEditor(int $gameId): void
    {
        $this->authorizeCommissioner();
        $this->resetValidation();
        $this->errorBanner = null;

        $game = Game::query()->findOrFail($gameId);

        $this->editingGameId = $game->id;
        $this->homeScore = $game->home_score;
        $this->awayScore = $game->away_score;
        $this->gameStatus = $game->status === GameStatus::Scheduled
            ? GameStatus::Finished->value
            : $game->status->value;
    }

    public function saveScore(): void
    {
        $this->authorizeCommissioner();

        $validated = $this->validate([
            'homeScore' => ['required', 'integer', 'min:0', 'max:300'],
            'awayScore' => ['required', 'integer', 'min:0', 'max:300'],
            'gameStatus' => ['required', Rule::enum(GameStatus::class)],
        ]);

        if ($validated['gameStatus'] === GameStatus::Finished->value && $validated['homeScore'] === $validated['awayScore']) {
            $this->addError('awayScore', 'Basketball games cannot finish in a tie. Enter the overtime final score.');

            return;
        }

        $game = Game::query()->findOrFail($this->editingGameId);
        $game->update([
            'home_score' => $validated['homeScore'],
            'away_score' => $validated['awayScore'],
            'status' => $validated['gameStatus'],
            'current_quarter' => $validated['gameStatus'] === GameStatus::Finished->value ? 4 : $game->current_quarter,
        ]);

        $this->editingGameId = null;
        session()->flash('status', 'Game result updated and standings recalculated.');
    }

    public function cancelScoreEditor(): void
    {
        $this->editingGameId = null;
        $this->resetValidation();
    }

    public function render(StandingsCalculator $standingsCalculator): View
    {
        $tournaments = Tournament::query()->withCount('teams')->orderBy('name')->get();
        $selectedTournament = $this->selectedTournamentId !== null
            ? Tournament::query()->with('teams')->find($this->selectedTournamentId)
            : null;

        $games = $selectedTournament !== null
            ? $selectedTournament->games()
                ->with(['homeTeam', 'awayTeam'])
                ->orderBy('round_number')
                ->orderBy('scheduled_at')
                ->orderBy('id')
                ->get()
            : collect();

        $gamesByRound = $games->groupBy(fn (Game $game): int => $game->round_number ?? 1);

        $standings = $selectedTournament !== null
            ? $standingsCalculator->calculate($selectedTournament)
            : collect();

        $byeTeamsByRound = [];

        if ($selectedTournament !== null && $selectedTournament->teams->count() >= 3 && $selectedTournament->teams->count() % 2 === 1) {
            $allTeams = $selectedTournament->teams->keyBy('id');

            foreach ($gamesByRound as $roundNumber => $roundGames) {
                $playingIds = $roundGames->flatMap(fn (Game $game): array => [$game->home_team_id, $game->away_team_id])->all();
                $byeTeam = $allTeams->except($playingIds)->first();

                if ($byeTeam !== null) {
                    $byeTeamsByRound[$roundNumber] = $byeTeam;
                }
            }
        }

        return view('livewire.schedule.manage-schedule', [
            'tournaments' => $tournaments,
            'selectedTournament' => $selectedTournament,
            'gamesByRound' => $gamesByRound,
            'byeTeamsByRound' => $byeTeamsByRound,
            'standings' => $standings,
            'gameStatuses' => GameStatus::cases(),
        ]);
    }

    private function authorizeCommissioner(): void
    {
        $user = auth()->user();

        if ($user === null || ! in_array($user->role, [UserRole::Commissioner, UserRole::SuperAdmin], true)) {
            abort(403);
        }
    }
}
