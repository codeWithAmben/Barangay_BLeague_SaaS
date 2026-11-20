<?php

namespace App\Livewire\Awards;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Tournament;
use App\Services\GameScoringService;
use App\Services\SeasonAwardsService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Awards & Leaderboards')]
class ManageAwards extends Component
{
    #[Url(as: 'tournament')]
    public ?int $selectedTournamentId = null;

    /** @var ?array<string, mixed> */
    public ?array $posterData = null;

    public function mount(): void
    {
        if ($this->selectedTournamentId === null) {
            $this->selectedTournamentId = Tournament::query()->latest()->value('id');
        }
    }

    public function openPoster(int $gameId, GameScoringService $scoringService): void
    {
        $game = Game::query()
            ->with(['tenant', 'tournament', 'homeTeam', 'awayTeam', 'bestPlayer.team'])
            ->findOrFail($gameId);

        $boxScores = $scoringService->computePlayerBoxScores($game);
        $bestPlayer = $game->bestPlayer;

        if ($bestPlayer === null) {
            $topRow = $boxScores->first();
            $bestPlayer = $topRow['player'] ?? null;
        } else {
            $topRow = $boxScores->first(fn (array $row): bool => $row['player']->id === $bestPlayer->id);
        }

        if ($bestPlayer === null) {
            return;
        }

        $this->posterData = [
            'barangay' => $game->tenant->name,
            'tournament' => $game->tournament->name,
            'home_team' => $game->homeTeam->name,
            'away_team' => $game->awayTeam->name,
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
            'player_name' => $bestPlayer->fullName,
            'jersey_number' => $bestPlayer->jersey_number,
            'position' => $bestPlayer->position->value,
            'team_name' => $bestPlayer->team->name,
            'primary_color' => $bestPlayer->team->primary_color ?? '#ea580c',
            'pts' => $topRow['pts'] ?? 0,
            'reb' => $topRow['reb'] ?? 0,
            'ast' => $topRow['ast'] ?? 0,
            'stl' => $topRow['stl'] ?? 0,
            'blk' => $topRow['blk'] ?? 0,
            'eff' => $topRow['eff'] ?? 0,
        ];
    }

    public function closePoster(): void
    {
        $this->posterData = null;
    }

    public function render(SeasonAwardsService $awardsService): View
    {
        $tournaments = Tournament::query()->orderBy('name')->get();
        $selectedTournament = $this->selectedTournamentId !== null
            ? Tournament::query()->find($this->selectedTournamentId)
            : null;

        $awards = $selectedTournament !== null
            ? $awardsService->calculate($selectedTournament)
            : [
                'leaders' => collect(),
                'mvp' => null,
                'mythical_five' => collect(),
                'category_leaders' => [],
            ];

        $finishedGames = $selectedTournament !== null
            ? $selectedTournament->games()
                ->where('status', GameStatus::Finished)
                ->with(['homeTeam', 'awayTeam', 'bestPlayer'])
                ->latest('id')
                ->get()
            : collect();

        return view('livewire.awards.manage-awards', [
            'tournaments' => $tournaments,
            'selectedTournament' => $selectedTournament,
            'awards' => $awards,
            'finishedGames' => $finishedGames,
        ]);
    }
}
