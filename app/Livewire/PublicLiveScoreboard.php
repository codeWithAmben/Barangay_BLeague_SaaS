<?php

namespace App\Livewire;

use App\Models\Game;
use App\Services\GameScoringService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::guest')]
#[Title('Live Scoreboard')]
class PublicLiveScoreboard extends Component
{
    public Game $game;

    public bool $showPoster = false;

    public function mount(Game $game): void
    {
        $this->game = Game::withoutGlobalScopes()
            ->with(['tenant', 'tournament', 'homeTeam', 'awayTeam', 'bestPlayer'])
            ->findOrFail($game->id);
    }

    public function togglePoster(): void
    {
        $this->showPoster = ! $this->showPoster;
    }

    public function render(GameScoringService $scoringService): View
    {
        $this->game = Game::withoutGlobalScopes()
            ->with(['tenant', 'tournament', 'homeTeam', 'awayTeam', 'bestPlayer.team'])
            ->findOrFail($this->game->id);

        $boxScores = $scoringService->computePlayerBoxScores($this->game);
        $periodSummary = $scoringService->computePeriodSummary($this->game);

        $recentEvents = $this->game->events()
            ->withoutGlobalScopes()
            ->with(['player', 'team'])
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $bpgPoster = null;

        if ($this->game->bestPlayer !== null) {
            $topRow = $boxScores->first(fn (array $row): bool => $row['player']->id === $this->game->best_player_id);
            $bpgPoster = [
                'barangay' => $this->game->tenant->name,
                'tournament' => $this->game->tournament->name,
                'home_team' => $this->game->homeTeam->name,
                'away_team' => $this->game->awayTeam->name,
                'home_score' => $this->game->home_score,
                'away_score' => $this->game->away_score,
                'player_name' => $this->game->bestPlayer->fullName,
                'jersey_number' => $this->game->bestPlayer->jersey_number,
                'position' => $this->game->bestPlayer->position->value,
                'team_name' => $this->game->bestPlayer->team->name,
                'primary_color' => $this->game->bestPlayer->team->primary_color ?? '#ea580c',
                'pts' => $topRow['pts'] ?? 0,
                'reb' => $topRow['reb'] ?? 0,
                'ast' => $topRow['ast'] ?? 0,
                'stl' => $topRow['stl'] ?? 0,
                'blk' => $topRow['blk'] ?? 0,
                'eff' => $topRow['eff'] ?? 0,
            ];
        }

        return view('livewire.public-live-scoreboard', [
            'boxScores' => $boxScores,
            'periodSummary' => $periodSummary,
            'recentEvents' => $recentEvents,
            'homeBox' => $boxScores->where('team_id', $this->game->home_team_id),
            'awayBox' => $boxScores->where('team_id', $this->game->away_team_id),
            'bpgPoster' => $bpgPoster,
        ]);
    }
}
