<?php

namespace App\Services;

use App\Enums\GameStatus;
use App\Enums\TournamentFormat;
use App\Models\Game;
use App\Models\Team;
use App\Models\Tournament;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoundRobinScheduler
{
    /**
     * Generate Berger-table round-robin pairings for a collection of teams.
     *
     * @param  Collection<int, Team>  $teams
     * @return list<array{round_number: int, bye_team: ?Team, matches: list<array{home: Team, away: Team}>}>
     */
    public function buildPairings(Collection $teams, bool $doubleRoundRobin = false): array
    {
        $teamList = $teams->values()->all();

        if (count($teamList) < 2) {
            throw new InvalidArgumentException('At least 2 teams are required to generate a round-robin schedule.');
        }

        /** @var list<?Team> $slots */
        $slots = $teamList;

        if (count($slots) % 2 !== 0) {
            $slots[] = null;
        }

        $totalSlots = count($slots);
        $roundsPerCycle = $totalSlots - 1;
        $matchesPerRound = intdiv($totalSlots, 2);

        $firstCycle = [];

        for ($roundIndex = 0; $roundIndex < $roundsPerCycle; $roundIndex++) {
            $byeTeam = null;
            $matches = [];

            for ($pairIndex = 0; $pairIndex < $matchesPerRound; $pairIndex++) {
                $teamA = $slots[$pairIndex];
                $teamB = $slots[$totalSlots - 1 - $pairIndex];

                if ($teamA === null || $teamB === null) {
                    $byeTeam = $teamA ?? $teamB;

                    continue;
                }

                $swapHomeAway = $pairIndex === 0
                    ? ($roundIndex % 2 === 1)
                    : (($roundIndex + $pairIndex) % 2 === 1);

                $matches[] = $swapHomeAway
                    ? ['home' => $teamB, 'away' => $teamA]
                    : ['home' => $teamA, 'away' => $teamB];
            }

            $firstCycle[] = [
                'round_number' => $roundIndex + 1,
                'bye_team' => $byeTeam,
                'matches' => $matches,
            ];

            $slots = $this->rotateSlots($slots);
        }

        if (! $doubleRoundRobin) {
            return $firstCycle;
        }

        $secondCycle = [];

        foreach ($firstCycle as $round) {
            $reversedMatches = array_map(
                fn (array $match): array => [
                    'home' => $match['away'],
                    'away' => $match['home'],
                ],
                $round['matches']
            );

            $secondCycle[] = [
                'round_number' => $round['round_number'] + $roundsPerCycle,
                'bye_team' => $round['bye_team'],
                'matches' => $reversedMatches,
            ];
        }

        return array_merge($firstCycle, $secondCycle);
    }

    /**
     * Persist generated round-robin games for a tournament.
     *
     * @param  array{
     *     venue?: string|null,
     *     start_date?: string|null,
     *     first_tipoff?: string|null,
     *     games_per_day?: int,
     *     minutes_between_games?: int,
     *     days_between_gamedays?: int,
     *     replace_scheduled?: bool
     * }  $options
     * @return Collection<int, Game>
     */
    public function generateForTournament(Tournament $tournament, array $options = []): Collection
    {
        $teams = $tournament->teams()->orderBy('id')->get();
        $isDoubleRoundRobin = $tournament->format_type === TournamentFormat::DoubleRoundRobin;
        $rounds = $this->buildPairings($teams, $isDoubleRoundRobin);

        $venue = filled($options['venue'] ?? null) ? (string) $options['venue'] : 'Barangay Covered Court';
        $startDate = filled($options['start_date'] ?? null)
            ? CarbonImmutable::parse((string) $options['start_date'])
            : CarbonImmutable::now()->addDay();
        $firstTipoff = filled($options['first_tipoff'] ?? null) ? (string) $options['first_tipoff'] : '18:00';
        [$hour, $minute] = array_pad(explode(':', $firstTipoff), 2, '0');

        $gamesPerDay = max(1, (int) ($options['games_per_day'] ?? 2));
        $minutesBetweenGames = max(30, (int) ($options['minutes_between_games'] ?? 90));
        $daysBetweenGamedays = max(1, (int) ($options['days_between_gamedays'] ?? 1));
        $replaceScheduled = (bool) ($options['replace_scheduled'] ?? true);

        $baseDateTime = $startDate->setTime((int) $hour, (int) $minute, 0);

        return DB::transaction(function () use (
            $tournament,
            $rounds,
            $venue,
            $baseDateTime,
            $gamesPerDay,
            $minutesBetweenGames,
            $daysBetweenGamedays,
            $replaceScheduled
        ): Collection {
            if ($replaceScheduled) {
                $tournament->games()
                    ->where('status', GameStatus::Scheduled)
                    ->delete();
            }

            $created = collect();
            $slotIndex = 0;

            foreach ($rounds as $round) {
                foreach ($round['matches'] as $match) {
                    $dayOffset = intdiv($slotIndex, $gamesPerDay) * $daysBetweenGamedays;
                    $gameOfDay = $slotIndex % $gamesPerDay;
                    $scheduledAt = $baseDateTime
                        ->addDays($dayOffset)
                        ->addMinutes($gameOfDay * $minutesBetweenGames);

                    $created->push(Game::create([
                        'tenant_id' => $tournament->tenant_id,
                        'tournament_id' => $tournament->id,
                        'home_team_id' => $match['home']->id,
                        'away_team_id' => $match['away']->id,
                        'scheduled_at' => $scheduledAt,
                        'venue' => $venue,
                        'round_number' => $round['round_number'],
                        'status' => GameStatus::Scheduled,
                        'home_score' => 0,
                        'away_score' => 0,
                        'current_quarter' => 1,
                    ]));

                    $slotIndex++;
                }
            }

            return $created;
        });
    }

    /**
     * Keep index 0 fixed and rotate remaining slots clockwise for the Berger table.
     *
     * @param  list<?Team>  $slots
     * @return list<?Team>
     */
    private function rotateSlots(array $slots): array
    {
        $fixed = $slots[0];
        $rotating = array_slice($slots, 1);
        $last = array_pop($rotating);
        array_unshift($rotating, $last);

        return array_merge([$fixed], $rotating);
    }
}
