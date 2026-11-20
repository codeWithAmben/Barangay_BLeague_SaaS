<?php

namespace App\Livewire\Scorer;

use App\Enums\GameEventType;
use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use App\Services\GameScoringService;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::scorer')]
#[Title('Courtside Scorer Mode')]
class CourtsideScorer extends Component
{
    public Game $game;

    public ?int $selectedTeamId = null;

    public ?int $selectedPlayerId = null;

    public ?int $manualBestPlayerId = null;

    public bool $showEndGameModal = false;

    public ?string $errorBanner = null;

    public function mount(Game $game, GameScoringService $scoringService): void
    {
        $this->game = Game::query()->with(['tournament', 'homeTeam', 'awayTeam'])->findOrFail($game->id);
        $scoringService->ensureRostersInitialized($this->game);

        $this->selectedTeamId = $this->game->home_team_id;
        $this->manualBestPlayerId = $this->game->best_player_id;

        $firstStarter = $this->game->rosters()
            ->where('team_id', $this->selectedTeamId)
            ->where('is_on_court', true)
            ->value('player_id');

        $this->selectedPlayerId = $firstStarter;
    }

    public function selectPlayer(int $teamId, int $playerId): void
    {
        $this->errorBanner = null;
        $this->selectedTeamId = $teamId;
        $this->selectedPlayerId = $playerId;
    }

    public function startGame(GameScoringService $scoringService): void
    {
        $this->errorBanner = null;
        $this->game = $scoringService->startGame($this->game);
        $this->dispatch('play-logged', message: 'Tip-Off! Game is LIVE', teamSide: null, points: 0);
    }

    public function changeQuarter(int $quarter, GameScoringService $scoringService): void
    {
        $this->errorBanner = null;
        $this->game = $scoringService->setQuarter($this->game, $quarter);
    }

    public function logPlay(string $eventTypeValue, string $gameClock, GameScoringService $scoringService): void
    {
        $this->errorBanner = null;

        $eventType = GameEventType::tryFrom($eventTypeValue);

        if ($eventType === null) {
            $this->errorBanner = 'Unknown play action.';

            return;
        }

        $isTeamOnlyEvent = in_array($eventType, [GameEventType::Timeout, GameEventType::Violation], true);

        if ($this->selectedTeamId === null) {
            $this->errorBanner = 'Select a team first.';

            return;
        }

        if (! $isTeamOnlyEvent && $this->selectedPlayerId === null) {
            $this->errorBanner = 'Tap an on-court player first before logging a stat.';

            return;
        }

        $this->persistPlay(
            $this->selectedTeamId,
            $isTeamOnlyEvent ? null : $this->selectedPlayerId,
            $eventType,
            $gameClock,
            $scoringService,
        );
    }

    public function logPlayForPlayer(
        int $teamId,
        int $playerId,
        string $eventTypeValue,
        string $gameClock,
        GameScoringService $scoringService,
    ): void {
        $this->errorBanner = null;

        $eventType = GameEventType::tryFrom($eventTypeValue);

        if ($eventType === null) {
            $this->errorBanner = 'Unknown play action.';

            return;
        }

        $this->selectedTeamId = $teamId;
        $this->selectedPlayerId = $playerId;

        $this->persistPlay($teamId, $playerId, $eventType, $gameClock, $scoringService);
    }

    public function callTimeout(int $teamId, string $gameClock, GameScoringService $scoringService): void
    {
        $this->errorBanner = null;
        $this->persistPlay($teamId, null, GameEventType::Timeout, $gameClock, $scoringService);
    }

    public function substitute(int $teamId, int $playerInId, string $gameClock, GameScoringService $scoringService): void
    {
        $playerOutId = $this->selectedTeamId === $teamId ? $this->selectedPlayerId : null;

        if ($playerOutId === null) {
            $this->errorBanner = 'First tap the active on-court player you want to sub OUT.';

            return;
        }

        $this->swapPlayers($teamId, $playerOutId, $playerInId, $gameClock, $scoringService);
    }

    public function swapPlayers(
        int $teamId,
        int $playerOutId,
        int $playerInId,
        string $gameClock,
        GameScoringService $scoringService,
    ): void {
        $this->errorBanner = null;

        $team = Team::query()->findOrFail($teamId);
        $playerOut = Player::query()->findOrFail($playerOutId);
        $playerIn = Player::query()->findOrFail($playerInId);

        try {
            $scoringService->substitutePlayer(
                game: $this->game,
                team: $team,
                playerOut: $playerOut,
                playerIn: $playerIn,
                quarter: $this->game->current_quarter,
                gameClock: $gameClock,
            );

            $this->selectedTeamId = $teamId;
            $this->selectedPlayerId = $playerIn->id;
            $this->game->refresh();

            $this->dispatch(
                'play-logged',
                message: "Sub: #{$playerIn->jersey_number} {$playerIn->last_name} IN for #{$playerOut->jersey_number}",
                teamSide: null,
                points: 0,
            );
        } catch (InvalidArgumentException $exception) {
            $this->errorBanner = $exception->getMessage();
        }
    }

    /**
     * Perform multiple substitutions at once (timeouts & quarter breaks).
     *
     * @param  list<int>  $outPlayerIds
     * @param  list<int>  $inPlayerIds
     */
    public function swapBatch(
        int $teamId,
        array $outPlayerIds,
        array $inPlayerIds,
        string $gameClock,
        GameScoringService $scoringService,
    ): void {
        $this->errorBanner = null;
        $team = Team::query()->findOrFail($teamId);

        try {
            $count = $scoringService->substituteBatch(
                game: $this->game,
                team: $team,
                outPlayerIds: $outPlayerIds,
                inPlayerIds: $inPlayerIds,
                quarter: $this->game->current_quarter,
                gameClock: $gameClock,
            );

            $this->game->refresh();
            $this->dispatch(
                'play-logged',
                message: "Subbed {$count} ".($count === 1 ? 'player' : 'players')." for {$team->name}",
                teamSide: null,
                points: 0,
            );
        } catch (InvalidArgumentException $exception) {
            $this->errorBanner = $exception->getMessage();
        }
    }

    public function undoLastPlay(GameScoringService $scoringService): void
    {
        $this->errorBanner = null;
        $removed = $scoringService->undoLastEvent($this->game);

        if ($removed === null) {
            $this->errorBanner = 'No plays to undo.';

            return;
        }

        $this->game->refresh();
        $this->dispatch('play-logged', message: "Undid: {$removed->event_type->label()}", teamSide: null, points: 0);
    }

    /**
     * Sync queued plays collected while the courtside tablet had spotty internet.
     *
     * @param  list<array{teamId: int, playerId: ?int, eventType: string, quarter: int, gameClock: string}>  $queuedPlays
     */
    public function flushOfflineQueue(array $queuedPlays, GameScoringService $scoringService): void
    {
        $this->errorBanner = null;
        $synced = 0;

        foreach ($queuedPlays as $play) {
            $eventType = GameEventType::tryFrom((string) ($play['eventType'] ?? ''));

            if ($eventType === null || ! isset($play['teamId'])) {
                continue;
            }

            try {
                $scoringService->recordEvent(
                    game: $this->game,
                    teamId: (int) $play['teamId'],
                    playerId: isset($play['playerId']) ? (int) $play['playerId'] : null,
                    eventType: $eventType,
                    quarter: isset($play['quarter']) ? (int) $play['quarter'] : $this->game->current_quarter,
                    gameClock: isset($play['gameClock']) ? (string) $play['gameClock'] : null,
                );
                $synced++;
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        $this->game->refresh();

        if ($synced > 0) {
            $this->dispatch('play-logged', message: "Synced {$synced} queued plays", teamSide: null, points: 0);
        }
    }

    public function openEndGameModal(): void
    {
        $this->errorBanner = null;
        $this->showEndGameModal = true;
    }

    public function closeEndGameModal(): void
    {
        $this->showEndGameModal = false;
    }

    public function finalizeGame(GameScoringService $scoringService): void
    {
        $this->errorBanner = null;

        try {
            $this->game = $scoringService->finalizeGame($this->game, $this->manualBestPlayerId ?: null);
            $this->manualBestPlayerId = $this->game->best_player_id;
            $this->showEndGameModal = false;
            $this->dispatch('play-logged', message: 'Game finalized & BPG crowned!', teamSide: null, points: 0);
        } catch (InvalidArgumentException $exception) {
            $this->errorBanner = $exception->getMessage();
        }
    }

    public function render(GameScoringService $scoringService): View
    {
        $this->game->load(['tournament', 'homeTeam', 'awayTeam', 'bestPlayer']);

        $rosters = $this->game->rosters()
            ->with('player')
            ->get()
            ->sortBy(fn ($roster) => (int) $roster->player->jersey_number);

        $events = $this->game->events()
            ->with(['player', 'team'])
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $boxScores = $scoringService->computePlayerBoxScores($this->game);
        $periodSummary = $scoringService->computePeriodSummary($this->game);
        $rotations = $scoringService->computePlayerRotations($this->game);

        return view('livewire.scorer.courtside-scorer', [
            'homeOnCourt' => $rosters->where('team_id', $this->game->home_team_id)->where('is_on_court', true),
            'homeBench' => $rosters->where('team_id', $this->game->home_team_id)->where('is_on_court', false),
            'awayOnCourt' => $rosters->where('team_id', $this->game->away_team_id)->where('is_on_court', true),
            'awayBench' => $rosters->where('team_id', $this->game->away_team_id)->where('is_on_court', false),
            'events' => $events,
            'boxScores' => $boxScores,
            'periodSummary' => $periodSummary,
            'rotations' => $rotations,
            'selectedPlayer' => $this->selectedPlayerId ? Player::query()->find($this->selectedPlayerId) : null,
            'isFinished' => $this->game->status === GameStatus::Finished,
        ]);
    }

    private function persistPlay(
        int $teamId,
        ?int $playerId,
        GameEventType $eventType,
        string $gameClock,
        GameScoringService $scoringService,
    ): void {
        try {
            $event = $scoringService->recordEvent(
                game: $this->game,
                teamId: $teamId,
                playerId: $playerId,
                eventType: $eventType,
                quarter: $this->game->current_quarter,
                gameClock: $gameClock,
            );

            $this->game->refresh();

            $player = $playerId !== null ? Player::query()->find($playerId) : null;
            $teamName = $teamId === $this->game->home_team_id ? $this->game->homeTeam->name : $this->game->awayTeam->name;
            $subject = $player !== null ? "#{$player->jersey_number} {$player->last_name}" : $teamName;
            $teamSide = $teamId === $this->game->home_team_id ? 'home' : 'away';

            $this->dispatch(
                'play-logged',
                message: "{$subject} {$eventType->label()}",
                teamSide: $teamSide,
                points: $eventType->pointsValue(),
                eventId: $event->id,
            );
        } catch (InvalidArgumentException $exception) {
            $this->errorBanner = $exception->getMessage();
        }
    }
}
