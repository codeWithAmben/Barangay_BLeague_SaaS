<?php

namespace App\Services;

use App\Enums\GameEventType;
use App\Enums\GameStatus;
use App\Enums\PlayerPosition;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Support\Collection;

class SeasonAwardsService
{
    /**
     * Compute season leaderboards, MVP race, Mythical Five, and category leaders for a tournament.
     *
     * @return array{
     *     leaders: Collection<int, array<string, mixed>>,
     *     mvp: ?array<string, mixed>,
     *     mythical_five: Collection<int, array<string, mixed>>,
     *     category_leaders: array<string, ?array<string, mixed>>
     * }
     */
    public function calculate(Tournament $tournament): array
    {
        $finishedGames = $tournament->games()
            ->where('status', GameStatus::Finished)
            ->with(['rosters', 'events'])
            ->get();

        $players = Player::withoutGlobalScopes()
            ->whereIn('team_id', $tournament->teams()->select('id'))
            ->with('team')
            ->get()
            ->keyBy('id');

        if ($players->isEmpty() || $finishedGames->isEmpty()) {
            return [
                'leaders' => collect(),
                'mvp' => null,
                'mythical_five' => collect(),
                'category_leaders' => [
                    'scoring' => null,
                    'rebounds' => null,
                    'assists' => null,
                    'steals' => null,
                    'blocks' => null,
                ],
            ];
        }

        /** @var array<int, array<string, mixed>> $stats */
        $stats = [];

        foreach ($players as $playerId => $player) {
            $stats[$playerId] = [
                'player' => $player,
                'team' => $player->team,
                'games_played' => 0,
                'pts' => 0,
                'reb' => 0,
                'ast' => 0,
                'stl' => 0,
                'blk' => 0,
                'to' => 0,
                'pf' => 0,
                'three_pm' => 0,
                'three_pa' => 0,
                'sp_total' => 0,
                'bpg_awards' => 0,
                'ppg' => 0.0,
                'rpg' => 0.0,
                'apg' => 0.0,
                'spg' => 0.0,
                'bpg' => 0.0,
                'three_pct' => 0.0,
                'sp_avg' => 0.0,
            ];
        }

        foreach ($finishedGames as $game) {
            if ($game->best_player_id !== null && isset($stats[$game->best_player_id])) {
                $stats[$game->best_player_id]['bpg_awards']++;
            }

            $participants = [];

            foreach ($game->rosters as $roster) {
                $participants[$roster->player_id] = true;
            }

            foreach ($game->events as $event) {
                if ($event->player_id === null || ! isset($stats[$event->player_id])) {
                    continue;
                }

                $pid = $event->player_id;
                $participants[$pid] = true;

                match ($event->event_type) {
                    GameEventType::Point1 => $stats[$pid]['pts'] += 1,
                    GameEventType::Point2 => $stats[$pid]['pts'] += 2,
                    GameEventType::Point3 => $this->incrementThreePointMade($stats[$pid]),
                    GameEventType::Missed3,
                    GameEventType::MissedShot => $stats[$pid]['three_pa']++,
                    GameEventType::ReboundOff,
                    GameEventType::ReboundDef => $stats[$pid]['reb']++,
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

            foreach (array_keys($participants) as $pid) {
                if (isset($stats[$pid])) {
                    $stats[$pid]['games_played']++;
                }
            }
        }

        $qualified = collect($stats)
            ->filter(fn (array $row): bool => $row['games_played'] > 0)
            ->map(function (array $row): array {
                $gp = $row['games_played'];
                $spTotal = $row['pts'] + $row['reb'] + $row['ast'] + $row['stl'] + $row['blk'] - $row['to'] - $row['pf'];

                $row['sp_total'] = $spTotal;
                $row['ppg'] = round($row['pts'] / $gp, 1);
                $row['rpg'] = round($row['reb'] / $gp, 1);
                $row['apg'] = round($row['ast'] / $gp, 1);
                $row['spg'] = round($row['stl'] / $gp, 1);
                $row['bpg'] = round($row['blk'] / $gp, 1);
                $row['sp_avg'] = round($spTotal / $gp, 1);
                $row['three_pct'] = $row['three_pa'] > 0
                    ? round(($row['three_pm'] / $row['three_pa']) * 100, 1)
                    : 0.0;

                return $row;
            })
            ->sort(function (array $a, array $b): int {
                $spCmp = $b['sp_avg'] <=> $a['sp_avg'];

                if ($spCmp !== 0) {
                    return $spCmp;
                }

                return $b['ppg'] <=> $a['ppg'];
            })
            ->values();

        $mythicalFive = $this->selectMythicalFive($qualified);

        return [
            'leaders' => $qualified,
            'mvp' => $qualified->first(),
            'mythical_five' => $mythicalFive,
            'category_leaders' => [
                'scoring' => $qualified->sortByDesc('ppg')->first(),
                'rebounds' => $qualified->sortByDesc('rpg')->first(),
                'assists' => $qualified->sortByDesc('apg')->first(),
                'steals' => $qualified->sortByDesc('spg')->first(),
                'blocks' => $qualified->sortByDesc('bpg')->first(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function incrementThreePointMade(array &$row): void
    {
        $row['pts'] += 3;
        $row['three_pm']++;
        $row['three_pa']++;
    }

    /**
     * Pick top player at each basketball position (PG, SG, SF, PF, C) by SP/G,
     * then fill any remaining slots up to 5 with the highest overall SP/G players.
     *
     * @param  Collection<int, array<string, mixed>>  $qualified
     * @return Collection<int, array<string, mixed>>
     */
    private function selectMythicalFive(Collection $qualified): Collection
    {
        $selected = collect();
        $selectedIds = [];

        foreach (PlayerPosition::cases() as $position) {
            $match = $qualified->first(
                fn (array $row): bool => $row['player']->position === $position
                    && ! in_array($row['player']->id, $selectedIds, true)
            );

            if ($match !== null) {
                $selected->push($match);
                $selectedIds[] = $match['player']->id;
            }
        }

        foreach ($qualified as $row) {
            if ($selected->count() >= 5) {
                break;
            }

            if (! in_array($row['player']->id, $selectedIds, true)) {
                $selected->push($row);
                $selectedIds[] = $row['player']->id;
            }
        }

        return $selected->values();
    }
}
