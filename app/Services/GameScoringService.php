<?php

namespace App\Services;

use App\Enums\GameEventType;
use App\Enums\GameStatus;
use App\Events\GameUpdated;
use App\Models\Game;
use App\Models\GameEvent;
use App\Models\GameRoster;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class GameScoringService
{
    public function ensureRostersInitialized(Game $game): void
    {
        if ($game->rosters()->exists()) {
            return;
        }

        $game->loadMissing(['homeTeam.players', 'awayTeam.players']);

        DB::transaction(function () use ($game): void {
            foreach ([$game->homeTeam, $game->awayTeam] as $team) {
                $players = $team->players()
                    ->get()
                    ->sortBy(fn (Player $player): int => (int) $player->jersey_number);

                $startersAssigned = 0;

                foreach ($players as $player) {
                    $isStarter = ! $player->is_suspended && $startersAssigned < 5;

                    if ($isStarter) {
                        $startersAssigned++;
                    }

                    GameRoster::create([
                        'tenant_id' => $game->tenant_id,
                        'game_id' => $game->id,
                        'player_id' => $player->id,
                        'team_id' => $team->id,
                        'is_starting' => $isStarter,
                        'is_on_court' => $isStarter,
                        'fouls_count' => 0,
                        'is_ejected' => false,
                    ]);
                }
            }
        });
    }

    public function startGame(Game $game): Game
    {
        $this->ensureRostersInitialized($game);

        if ($game->status === GameStatus::Scheduled) {
            $game->update([
                'status' => GameStatus::Live,
                'current_quarter' => max(1, $game->current_quarter),
            ]);
            $this->broadcastUpdate($game);
        }

        return $game->fresh();
    }

    public function setQuarter(Game $game, int $quarter): Game
    {
        $clamped = max(1, min(6, $quarter));
        $game->update(['current_quarter' => $clamped]);
        $this->broadcastUpdate($game);

        return $game->fresh();
    }

    public function recordEvent(
        Game $game,
        int $teamId,
        ?int $playerId,
        GameEventType $eventType,
        ?int $quarter = null,
        ?string $gameClock = null,
    ): GameEvent {
        if (! in_array($teamId, [$game->home_team_id, $game->away_team_id], true)) {
            throw new InvalidArgumentException('Selected team does not belong to this game.');
        }

        if ($playerId !== null) {
            $belongsToTeam = Player::withoutGlobalScopes()
                ->where('id', $playerId)
                ->where('team_id', $teamId)
                ->exists();

            if (! $belongsToTeam) {
                throw new InvalidArgumentException('Selected player does not belong to the specified team.');
            }
        }

        $this->ensureRostersInitialized($game);

        $event = DB::transaction(function () use ($game, $teamId, $playerId, $eventType, $quarter, $gameClock): GameEvent {
            if ($game->status === GameStatus::Scheduled) {
                $game->update(['status' => GameStatus::Live]);
            }

            $event = GameEvent::create([
                'tenant_id' => $game->tenant_id,
                'game_id' => $game->id,
                'team_id' => $teamId,
                'player_id' => $playerId,
                'quarter' => $quarter ?? $game->current_quarter,
                'game_clock' => $gameClock,
                'event_type' => $eventType,
            ]);

            $this->recalculateFromEvents($game);

            if ($eventType === GameEventType::FoulTechnical && $playerId !== null) {
                $this->applyTechnicalSuspensionRule($game->tournament_id, $playerId);
            }

            return $event;
        });

        $this->broadcastUpdate($game->fresh());

        return $event;
    }

    public function substitutePlayer(
        Game $game,
        Team $team,
        Player $playerOut,
        Player $playerIn,
        ?int $quarter = null,
        ?string $gameClock = null,
    ): void {
        if (! in_array($team->id, [$game->home_team_id, $game->away_team_id], true)) {
            throw new InvalidArgumentException('Team does not belong to this game.');
        }

        if ($playerOut->team_id !== $team->id || $playerIn->team_id !== $team->id) {
            throw new InvalidArgumentException('Both players must belong to the same team.');
        }

        if ($playerOut->id === $playerIn->id) {
            throw new InvalidArgumentException('Select a different bench player to substitute.');
        }

        $this->ensureRostersInitialized($game);

        $rosterOut = GameRoster::withoutGlobalScopes()
            ->where('game_id', $game->id)
            ->where('player_id', $playerOut->id)
            ->firstOrFail();

        if (! $rosterOut->is_on_court) {
            throw new InvalidArgumentException("{$playerOut->fullName} is not currently on the court.");
        }

        $rosterIn = GameRoster::withoutGlobalScopes()
            ->where('game_id', $game->id)
            ->where('player_id', $playerIn->id)
            ->firstOrFail();

        if ($rosterIn->is_on_court) {
            throw new InvalidArgumentException("{$playerIn->fullName} is already on the court.");
        }

        if ($rosterIn->is_ejected) {
            throw new InvalidArgumentException("{$playerIn->fullName} has fouled out or been ejected and cannot re-enter.");
        }

        if ($playerIn->is_suspended) {
            throw new InvalidArgumentException("{$playerIn->fullName} is suspended for this game.");
        }

        $onCourtCount = GameRoster::withoutGlobalScopes()
            ->where('game_id', $game->id)
            ->where('team_id', $team->id)
            ->where('is_on_court', true)
            ->count();

        if ($onCourtCount > 5) {
            throw new InvalidArgumentException('Cannot exceed 5 players on the court.');
        }

        DB::transaction(function () use ($game, $team, $playerOut, $playerIn, $quarter, $gameClock): void {
            $q = $quarter ?? $game->current_quarter;

            GameEvent::create([
                'tenant_id' => $game->tenant_id,
                'game_id' => $game->id,
                'team_id' => $team->id,
                'player_id' => $playerOut->id,
                'quarter' => $q,
                'game_clock' => $gameClock,
                'event_type' => GameEventType::SubOut,
            ]);

            GameEvent::create([
                'tenant_id' => $game->tenant_id,
                'game_id' => $game->id,
                'team_id' => $team->id,
                'player_id' => $playerIn->id,
                'quarter' => $q,
                'game_clock' => $gameClock,
                'event_type' => GameEventType::SubIn,
            ]);

            $this->recalculateFromEvents($game);
        });

        $this->broadcastUpdate($game->fresh());
    }

    /**
     * Perform multiple substitutions atomically (e.g., during timeouts or quarter breaks).
     *
     * @param  list<int>  $outPlayerIds
     * @param  list<int>  $inPlayerIds
     */
    public function substituteBatch(
        Game $game,
        Team $team,
        array $outPlayerIds,
        array $inPlayerIds,
        ?int $quarter = null,
        ?string $gameClock = null,
    ): int {
        if (! in_array($team->id, [$game->home_team_id, $game->away_team_id], true)) {
            throw new InvalidArgumentException('Team does not belong to this game.');
        }

        $outIds = array_values(array_unique(array_map('intval', $outPlayerIds)));
        $inIds = array_values(array_unique(array_map('intval', $inPlayerIds)));

        if (count($outIds) === 0 || count($outIds) !== count($inIds)) {
            throw new InvalidArgumentException('Select an equal number of On-Court (Out) and Bench (In) players.');
        }

        if (count(array_intersect($outIds, $inIds)) > 0) {
            throw new InvalidArgumentException('A player cannot be subbed both out and in.');
        }

        $this->ensureRostersInitialized($game);

        $rosters = GameRoster::withoutGlobalScopes()
            ->where('game_id', $game->id)
            ->where('team_id', $team->id)
            ->with('player')
            ->get()
            ->keyBy('player_id');

        foreach ($outIds as $outId) {
            $rosterOut = $rosters->get($outId);
            if ($rosterOut === null || ! $rosterOut->is_on_court) {
                throw new InvalidArgumentException('All outgoing players must currently be on the court.');
            }
        }

        foreach ($inIds as $inId) {
            $rosterIn = $rosters->get($inId);
            if ($rosterIn === null || $rosterIn->is_on_court) {
                throw new InvalidArgumentException('All incoming players must currently be on the bench.');
            }
            if ($rosterIn->is_ejected || $rosterIn->player->is_suspended) {
                throw new InvalidArgumentException("{$rosterIn->player->fullName} is ineligible to enter the game.");
            }
        }

        $q = $quarter ?? $game->current_quarter;

        DB::transaction(function () use ($game, $team, $outIds, $inIds, $q, $gameClock): void {
            foreach ($outIds as $index => $outId) {
                $inId = $inIds[$index];

                GameEvent::create([
                    'tenant_id' => $game->tenant_id,
                    'game_id' => $game->id,
                    'team_id' => $team->id,
                    'player_id' => $outId,
                    'quarter' => $q,
                    'game_clock' => $gameClock,
                    'event_type' => GameEventType::SubOut,
                ]);

                GameEvent::create([
                    'tenant_id' => $game->tenant_id,
                    'game_id' => $game->id,
                    'team_id' => $team->id,
                    'player_id' => $inId,
                    'quarter' => $q,
                    'game_clock' => $gameClock,
                    'event_type' => GameEventType::SubIn,
                ]);
            }

            $this->recalculateFromEvents($game);
        });

        $this->broadcastUpdate($game->fresh());

        return count($outIds);
    }

    public function undoLastEvent(Game $game): ?GameEvent
    {
        $lastEvent = $game->events()->orderByDesc('id')->first();

        if ($lastEvent === null) {
            return null;
        }

        DB::transaction(function () use ($game, $lastEvent): void {
            if ($lastEvent->event_type === GameEventType::SubIn) {
                $pairedSubOut = $game->events()
                    ->where('id', '<', $lastEvent->id)
                    ->orderByDesc('id')
                    ->first();

                if ($pairedSubOut !== null
                    && $pairedSubOut->event_type === GameEventType::SubOut
                    && $pairedSubOut->team_id === $lastEvent->team_id
                    && $pairedSubOut->quarter === $lastEvent->quarter) {
                    $pairedSubOut->delete();
                }
            }

            $lastEvent->delete();
            $this->recalculateFromEvents($game);
        });

        $this->broadcastUpdate($game->fresh());

        return $lastEvent;
    }

    /**
     * Compute rotation status / last substitution timestamp label per player in a game.
     *
     * @return array<int, string>
     */
    public function computePlayerRotations(Game $game): array
    {
        $rosters = $game->rosters()->get();
        $events = $game->events()
            ->whereIn('event_type', [GameEventType::SubIn, GameEventType::SubOut])
            ->orderBy('id')
            ->get();

        $labels = [];

        foreach ($rosters as $roster) {
            $labels[$roster->player_id] = $roster->is_starting ? 'Starter' : 'Bench';
        }

        foreach ($events as $event) {
            if ($event->player_id === null) {
                continue;
            }

            $qLabel = self::quarterLabel($event->quarter);
            $clock = $event->game_clock ? " {$event->game_clock}" : '';
            $prefix = $event->event_type === GameEventType::SubIn ? 'In' : 'Out';

            $labels[$event->player_id] = "{$prefix} {$qLabel}{$clock}";
        }

        return $labels;
    }

    public function recalculateFromEvents(Game $game): void
    {
        $events = $game->events()->orderBy('id')->get();

        $homeScore = 0;
        $awayScore = 0;

        /** @var array<int, array{p: int, t: int, u: int, d: int}> $foulsByPlayer */
        $foulsByPlayer = [];

        foreach ($events as $event) {
            $pts = $event->event_type->pointsValue();

            if ($pts > 0) {
                if ($event->team_id === $game->home_team_id) {
                    $homeScore += $pts;
                } elseif ($event->team_id === $game->away_team_id) {
                    $awayScore += $pts;
                }
            }

            if ($event->player_id !== null && $event->event_type->isFoul()) {
                $pid = $event->player_id;
                $foulsByPlayer[$pid] ??= ['p' => 0, 't' => 0, 'u' => 0, 'd' => 0];

                match ($event->event_type) {
                    GameEventType::FoulPersonal => $foulsByPlayer[$pid]['p']++,
                    GameEventType::FoulTechnical => $foulsByPlayer[$pid]['t']++,
                    GameEventType::FoulUnsportsmanlike => $foulsByPlayer[$pid]['u']++,
                    GameEventType::FoulDisqualifying => $foulsByPlayer[$pid]['d']++,
                    default => null,
                };
            }
        }

        $game->update([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
        ]);

        $rosters = $game->rosters()->get();

        /** @var array<int, bool> $onCourtState */
        $onCourtState = [];

        foreach ($rosters as $roster) {
            $onCourtState[$roster->player_id] = $roster->is_starting;
        }

        foreach ($events as $event) {
            if ($event->player_id === null) {
                continue;
            }

            if ($event->event_type === GameEventType::SubIn) {
                $onCourtState[$event->player_id] = true;
            } elseif ($event->event_type === GameEventType::SubOut) {
                $onCourtState[$event->player_id] = false;
            }
        }

        foreach ($rosters as $roster) {
            $foulCounts = $foulsByPlayer[$roster->player_id] ?? ['p' => 0, 't' => 0, 'u' => 0, 'd' => 0];
            $totalFouls = $foulCounts['p'] + $foulCounts['t'] + $foulCounts['u'];
            $isEjected = $totalFouls >= 5
                || $foulCounts['t'] >= 2
                || $foulCounts['u'] >= 2
                || $foulCounts['d'] >= 1;

            $isOnCourt = $isEjected ? false : ($onCourtState[$roster->player_id] ?? $roster->is_starting);

            $roster->update([
                'fouls_count' => $totalFouls,
                'is_ejected' => $isEjected,
                'is_on_court' => $isOnCourt,
            ]);
        }
    }

    /**
     * Compute player box scores and FIBA Efficiency (EFF) for a game.
     *
     * @return Collection<int, array{
     *     player: Player,
     *     team_id: int,
     *     pts: int,
     *     reb_off: int,
     *     reb_def: int,
     *     reb: int,
     *     ast: int,
     *     stl: int,
     *     blk: int,
     *     to: int,
     *     pf: int,
     *     eff: int
     * }>
     */
    public function computePlayerBoxScores(Game $game): Collection
    {
        $rosters = $game->rosters()->with('player')->get();
        $events = $game->events()->get();

        $stats = [];

        foreach ($rosters as $roster) {
            $stats[$roster->player_id] = [
                'player' => $roster->player,
                'team_id' => $roster->team_id,
                'pts' => 0,
                'reb_off' => 0,
                'reb_def' => 0,
                'reb' => 0,
                'ast' => 0,
                'stl' => 0,
                'blk' => 0,
                'to' => 0,
                'pf' => 0,
                'eff' => 0,
            ];
        }

        foreach ($events as $event) {
            if ($event->player_id === null || ! isset($stats[$event->player_id])) {
                continue;
            }

            $pid = $event->player_id;

            match ($event->event_type) {
                GameEventType::Point1 => $stats[$pid]['pts'] += 1,
                GameEventType::Point2 => $stats[$pid]['pts'] += 2,
                GameEventType::Point3 => $stats[$pid]['pts'] += 3,
                GameEventType::ReboundOff => $stats[$pid]['reb_off']++,
                GameEventType::ReboundDef => $stats[$pid]['reb_def']++,
                GameEventType::Assist => $stats[$pid]['ast']++,
                GameEventType::Steal => $stats[$pid]['stl']++,
                GameEventType::Block => $stats[$pid]['blk']++,
                GameEventType::Turnover => $stats[$pid]['to']++,
                GameEventType::FoulPersonal,
                GameEventType::FoulTechnical,
                GameEventType::FoulUnsportsmanlike,
                GameEventType::FoulDisqualifying => $stats[$pid]['pf']++,
                default => null,
            };
        }

        foreach ($stats as $pid => $row) {
            $reb = $row['reb_off'] + $row['reb_def'];
            $stats[$pid]['reb'] = $reb;
            $stats[$pid]['eff'] = $row['pts']
                + $reb
                + $row['ast']
                + $row['stl']
                + $row['blk']
                - $row['to']
                - $row['pf'];
        }

        return collect($stats)
            ->sortByDesc('eff')
            ->sortByDesc('pts')
            ->values();
    }

    /**
     * Compute quarter-by-quarter scores, team fouls (for penalty flags), and remaining timeouts.
     *
     * @return array{
     *     quarters: array<int, array{home: int, away: int}>,
     *     current_quarter_fouls: array{home: int, away: int},
     *     home_penalty: bool,
     *     away_penalty: bool,
     *     timeouts_remaining: array{home: int, away: int}
     * }
     */
    public function computePeriodSummary(Game $game): array
    {
        $events = $game->events()->get();
        $maxQuarter = max(4, $game->current_quarter);

        $quarters = [];

        for ($q = 1; $q <= $maxQuarter; $q++) {
            $quarters[$q] = ['home' => 0, 'away' => 0];
        }

        $currentFouls = ['home' => 0, 'away' => 0];
        $halfTimeoutsUsed = ['home' => 0, 'away' => 0];

        $isFirstHalf = $game->current_quarter <= 2;
        $isSecondHalf = $game->current_quarter === 3 || $game->current_quarter === 4;

        foreach ($events as $event) {
            $side = $event->team_id === $game->home_team_id ? 'home' : 'away';
            $pts = $event->event_type->pointsValue();

            if ($pts > 0) {
                $quarters[$event->quarter] ??= ['home' => 0, 'away' => 0];
                $quarters[$event->quarter][$side] += $pts;
            }

            if ($event->quarter === $game->current_quarter && $event->event_type->isFoul()) {
                $currentFouls[$side]++;
            }

            if ($event->event_type === GameEventType::Timeout) {
                $inCurrentPeriodBucket = ($isFirstHalf && $event->quarter <= 2)
                    || ($isSecondHalf && ($event->quarter === 3 || $event->quarter === 4))
                    || ($game->current_quarter >= 5 && $event->quarter === $game->current_quarter);

                if ($inCurrentPeriodBucket) {
                    $halfTimeoutsUsed[$side]++;
                }
            }
        }

        $maxTimeouts = $isFirstHalf ? 2 : ($isSecondHalf ? 3 : 1);

        return [
            'quarters' => $quarters,
            'current_quarter_fouls' => $currentFouls,
            'home_penalty' => $currentFouls['home'] >= 5,
            'away_penalty' => $currentFouls['away'] >= 5,
            'timeouts_remaining' => [
                'home' => max(0, $maxTimeouts - $halfTimeoutsUsed['home']),
                'away' => max(0, $maxTimeouts - $halfTimeoutsUsed['away']),
            ],
        ];
    }

    public function finalizeGame(Game $game, ?int $manualBestPlayerId = null): Game
    {
        $game->refresh();

        if ($game->home_score === $game->away_score) {
            throw new InvalidArgumentException('Cannot finalize a tied basketball game. Advance to OT until a winner is decided.');
        }

        $bestPlayerId = $manualBestPlayerId;

        if ($bestPlayerId === null) {
            $winningTeamId = $game->home_score > $game->away_score
                ? $game->home_team_id
                : $game->away_team_id;

            $boxScores = $this->computePlayerBoxScores($game);
            $winningTop = $boxScores
                ->where('team_id', $winningTeamId)
                ->sortByDesc('eff')
                ->first();

            $bestPlayerId = $winningTop['player']->id ?? $boxScores->sortByDesc('eff')->first()['player']->id ?? null;
        }

        $game->update([
            'status' => GameStatus::Finished,
            'best_player_id' => $bestPlayerId,
        ]);

        $this->broadcastUpdate($game->fresh());

        return $game->fresh();
    }

    public static function quarterLabel(int $quarter): string
    {
        return match ($quarter) {
            1 => 'Q1',
            2 => 'Q2',
            3 => 'Q3',
            4 => 'Q4',
            default => 'OT'.($quarter - 4),
        };
    }

    private function applyTechnicalSuspensionRule(int $tournamentId, int $playerId): void
    {
        $techCount = GameEvent::withoutGlobalScopes()
            ->where('player_id', $playerId)
            ->where('event_type', GameEventType::FoulTechnical)
            ->whereHas('game', fn ($query) => $query->where('tournament_id', $tournamentId))
            ->count();

        if ($techCount >= 2) {
            Player::withoutGlobalScopes()
                ->where('id', $playerId)
                ->update(['is_suspended' => true]);
        }
    }

    private function broadcastUpdate(Game $game): void
    {
        try {
            broadcast(new GameUpdated($game, $this->computePeriodSummary($game)));
        } catch (Throwable) {
            // Do not block courtside stat logging if WebSocket server is temporarily unreachable.
        }
    }
}
