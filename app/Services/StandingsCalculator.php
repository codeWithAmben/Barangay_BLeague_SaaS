<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Support\Collection;

class StandingsCalculator
{
    /**
     * Calculate FIBA-ordered tournament standings with head-to-head, quotient, and point differential tie-breakers.
     *
     * @return Collection<int, array{
     *     rank: int,
     *     team: Team,
     *     games_played: int,
     *     wins: int,
     *     losses: int,
     *     points: int,
     *     win_percentage: float,
     *     points_for: int,
     *     points_against: int,
     *     point_differential: int,
     *     quotient: float,
     *     tie_breaker_note: ?string
     * }>
     */
    public function calculate(Tournament $tournament): Collection
    {
        $teams = $tournament->teams()->orderBy('name')->get();

        if ($teams->isEmpty()) {
            return collect();
        }

        $finishedGames = $tournament->games()
            ->where('status', GameStatus::Finished)
            ->get();

        $stats = [];

        foreach ($teams as $team) {
            $stats[$team->id] = [
                'team' => $team,
                'games_played' => 0,
                'wins' => 0,
                'losses' => 0,
                'points' => 0,
                'win_percentage' => 0.0,
                'points_for' => 0,
                'points_against' => 0,
                'point_differential' => 0,
                'quotient' => 0.0,
                'tie_breaker_note' => null,
            ];
        }

        foreach ($finishedGames as $game) {
            if (! isset($stats[$game->home_team_id], $stats[$game->away_team_id])) {
                continue;
            }

            if ($game->home_score === $game->away_score) {
                continue;
            }

            $homeWon = $game->home_score > $game->away_score;

            $stats[$game->home_team_id]['games_played']++;
            $stats[$game->away_team_id]['games_played']++;

            $stats[$game->home_team_id]['points_for'] += $game->home_score;
            $stats[$game->home_team_id]['points_against'] += $game->away_score;
            $stats[$game->away_team_id]['points_for'] += $game->away_score;
            $stats[$game->away_team_id]['points_against'] += $game->home_score;

            if ($homeWon) {
                $stats[$game->home_team_id]['wins']++;
                $stats[$game->home_team_id]['points'] += 2;
                $stats[$game->away_team_id]['losses']++;
                $stats[$game->away_team_id]['points'] += 1;
            } else {
                $stats[$game->away_team_id]['wins']++;
                $stats[$game->away_team_id]['points'] += 2;
                $stats[$game->home_team_id]['losses']++;
                $stats[$game->home_team_id]['points'] += 1;
            }
        }

        foreach ($stats as $teamId => $row) {
            $gp = $row['games_played'];
            $pf = $row['points_for'];
            $pa = $row['points_against'];

            $stats[$teamId]['win_percentage'] = $gp > 0 ? round($row['wins'] / $gp, 3) : 0.0;
            $stats[$teamId]['point_differential'] = $pf - $pa;
            $stats[$teamId]['quotient'] = $pa > 0
                ? round($pf / $pa, 3)
                : ($pf > 0 ? (float) $pf : 0.0);
        }

        $groupedByPoints = collect($stats)
            ->sortByDesc('points')
            ->groupBy('points');

        $orderedRows = [];

        foreach ($groupedByPoints as $group) {
            $resolved = $this->resolveTiedGroup($group->values()->all(), $finishedGames);

            foreach ($resolved as $row) {
                $orderedRows[] = $row;
            }
        }

        return collect($orderedRows)
            ->values()
            ->map(function (array $row, int $index): array {
                $row['rank'] = $index + 1;

                return $row;
            });
    }

    /**
     * Resolve ties using FIBA order: head-to-head record, head-to-head differential,
     * overall quotient (PF / PA), overall point differential, overall PF, then name.
     *
     * @param  list<array<string, mixed>>  $group
     * @param  Collection<int, Game>  $finishedGames
     * @return list<array<string, mixed>>
     */
    private function resolveTiedGroup(array $group, Collection $finishedGames, bool $allowH2hRecursion = true): array
    {
        if (count($group) <= 1) {
            return $group;
        }

        $tiedIds = array_map(fn (array $row): int => $row['team']->id, $group);
        $h2h = $this->computeHeadToHeadStats($tiedIds, $finishedGames);
        $hasH2hGames = collect($h2h)->sum('games') > 0;

        if ($allowH2hRecursion && $hasH2hGames) {
            // Group by H2H classification points first; if a subset remains tied, resolve by H2H diff or overall quotient.
            $byH2hPoints = [];

            foreach ($group as $row) {
                $id = $row['team']->id;
                $h2hPts = $h2h[$id]['points'];
                $row['tie_breaker_note'] = 'Head-to-head';
                $byH2hPoints[$h2hPts][] = $row;
            }

            krsort($byH2hPoints);

            // If H2H points did not split the entire group at all, sort directly by H2H diff -> overall quotient -> overall diff.
            if (count($byH2hPoints) === 1) {
                return $this->sortWithH2hDiffAndOverallMetrics($group, $h2h);
            }

            $resolved = [];

            foreach ($byH2hPoints as $subGroup) {
                if (count($subGroup) === 1) {
                    $resolved[] = $subGroup[0];

                    continue;
                }

                // Recurse once on the remaining tied subset with its own mini-league H2H if smaller than original group.
                if (count($subGroup) < count($group)) {
                    foreach ($this->resolveTiedGroup($subGroup, $finishedGames, true) as $item) {
                        $resolved[] = $item;
                    }
                } else {
                    foreach ($this->sortWithH2hDiffAndOverallMetrics($subGroup, $h2h) as $item) {
                        $resolved[] = $item;
                    }
                }
            }

            return $resolved;
        }

        return $this->sortByOverallMetrics($group);
    }

    /**
     * @param  list<int>  $teamIds
     * @param  Collection<int, Game>  $finishedGames
     * @return array<int, array{games: int, points: int, diff: int, pf: int}>
     */
    private function computeHeadToHeadStats(array $teamIds, Collection $finishedGames): array
    {
        $h2h = [];

        foreach ($teamIds as $id) {
            $h2h[$id] = [
                'games' => 0,
                'points' => 0,
                'diff' => 0,
                'pf' => 0,
            ];
        }

        foreach ($finishedGames as $game) {
            if (! in_array($game->home_team_id, $teamIds, true) || ! in_array($game->away_team_id, $teamIds, true)) {
                continue;
            }

            if ($game->home_score === $game->away_score) {
                continue;
            }

            $h2h[$game->home_team_id]['games']++;
            $h2h[$game->away_team_id]['games']++;
            $h2h[$game->home_team_id]['pf'] += $game->home_score;
            $h2h[$game->away_team_id]['pf'] += $game->away_score;

            $diff = $game->home_score - $game->away_score;
            $h2h[$game->home_team_id]['diff'] += $diff;
            $h2h[$game->away_team_id]['diff'] -= $diff;

            if ($diff > 0) {
                $h2h[$game->home_team_id]['points'] += 2;
                $h2h[$game->away_team_id]['points'] += 1;
            } else {
                $h2h[$game->away_team_id]['points'] += 2;
                $h2h[$game->home_team_id]['points'] += 1;
            }
        }

        return $h2h;
    }

    /**
     * @param  list<array<string, mixed>>  $group
     * @param  array<int, array{games: int, points: int, diff: int, pf: int}>  $h2h
     * @return list<array<string, mixed>>
     */
    private function sortWithH2hDiffAndOverallMetrics(array $group, array $h2h): array
    {
        usort($group, function (array $a, array $b) use ($h2h): int {
            $idA = $a['team']->id;
            $idB = $b['team']->id;

            $h2hDiffCmp = $h2h[$idB]['diff'] <=> $h2h[$idA]['diff'];

            if ($h2hDiffCmp !== 0) {
                return $h2hDiffCmp;
            }

            $h2hPfCmp = $h2h[$idB]['pf'] <=> $h2h[$idA]['pf'];

            if ($h2hPfCmp !== 0) {
                return $h2hPfCmp;
            }

            return $this->compareOverallMetrics($a, $b);
        });

        return array_map(function (array $row) use ($h2h): array {
            $id = $row['team']->id;
            $row['tie_breaker_note'] = $h2h[$id]['games'] > 0 ? 'H2H Diff / Quotient' : 'Quotient';

            return $row;
        }, $group);
    }

    /**
     * @param  list<array<string, mixed>>  $group
     * @return list<array<string, mixed>>
     */
    private function sortByOverallMetrics(array $group): array
    {
        usort($group, fn (array $a, array $b): int => $this->compareOverallMetrics($a, $b));

        return array_map(function (array $row): array {
            if ($row['games_played'] > 0) {
                $row['tie_breaker_note'] = 'Quotient';
            }

            return $row;
        }, $group);
    }

    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private function compareOverallMetrics(array $a, array $b): int
    {
        $quotientCmp = $b['quotient'] <=> $a['quotient'];

        if ($quotientCmp !== 0) {
            return $quotientCmp;
        }

        $diffCmp = $b['point_differential'] <=> $a['point_differential'];

        if ($diffCmp !== 0) {
            return $diffCmp;
        }

        $pfCmp = $b['points_for'] <=> $a['points_for'];

        if ($pfCmp !== 0) {
            return $pfCmp;
        }

        return strcmp($a['team']->name, $b['team']->name);
    }
}
