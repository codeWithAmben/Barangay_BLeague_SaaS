<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Services\RoundRobinScheduler;
use InvalidArgumentException;
use Tests\TestCase;

class RoundRobinSchedulerTest extends TestCase
{
    public function test_even_number_of_teams_generates_berger_single_round_robin(): void
    {
        $teams = collect(range(1, 4))->map(function (int $i): Team {
            $team = new Team(['name' => "Team {$i}"]);
            $team->id = $i;

            return $team;
        });

        $rounds = app(RoundRobinScheduler::class)->buildPairings($teams);

        $this->assertCount(3, $rounds);

        $allMatchKeys = [];

        foreach ($rounds as $round) {
            $this->assertNull($round['bye_team']);
            $this->assertCount(2, $round['matches']);

            $roundTeamIds = [];

            foreach ($round['matches'] as $match) {
                $roundTeamIds[] = $match['home']->id;
                $roundTeamIds[] = $match['away']->id;

                $ids = [$match['home']->id, $match['away']->id];
                sort($ids);
                $allMatchKeys[] = implode('-', $ids);
            }

            $this->assertCount(4, array_unique($roundTeamIds));
        }

        $this->assertCount(6, array_unique($allMatchKeys));
    }

    public function test_odd_number_of_teams_assigns_exact_one_bye_per_team(): void
    {
        $teams = collect(range(1, 5))->map(function (int $i): Team {
            $team = new Team(['name' => "Purok {$i}"]);
            $team->id = $i;

            return $team;
        });

        $rounds = app(RoundRobinScheduler::class)->buildPairings($teams);

        $this->assertCount(5, $rounds);

        $byeIds = [];
        $allMatchKeys = [];

        foreach ($rounds as $round) {
            $this->assertNotNull($round['bye_team']);
            $this->assertCount(2, $round['matches']);
            $byeIds[] = $round['bye_team']->id;

            foreach ($round['matches'] as $match) {
                $this->assertNotSame($round['bye_team']->id, $match['home']->id);
                $this->assertNotSame($round['bye_team']->id, $match['away']->id);

                $ids = [$match['home']->id, $match['away']->id];
                sort($ids);
                $allMatchKeys[] = implode('-', $ids);
            }
        }

        $this->assertCount(5, array_unique($byeIds));
        $this->assertCount(10, array_unique($allMatchKeys));
    }

    public function test_double_round_robin_reverses_home_and_away_in_second_cycle(): void
    {
        $teams = collect(range(1, 4))->map(function (int $i): Team {
            $team = new Team(['name' => "Team {$i}"]);
            $team->id = $i;

            return $team;
        });

        $rounds = app(RoundRobinScheduler::class)->buildPairings($teams, doubleRoundRobin: true);

        $this->assertCount(6, $rounds);

        $directedPairs = [];

        foreach ($rounds as $round) {
            foreach ($round['matches'] as $match) {
                $directedPairs[] = "{$match['home']->id}->{$match['away']->id}";
            }
        }

        $this->assertCount(12, array_unique($directedPairs));
    }

    public function test_scheduler_requires_at_least_two_teams(): void
    {
        $team = new Team(['name' => 'Solo']);
        $team->id = 1;

        $this->expectException(InvalidArgumentException::class);

        app(RoundRobinScheduler::class)->buildPairings(collect([$team]));
    }
}
