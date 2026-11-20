<?php

namespace Tests\Unit;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Tournament;
use App\Services\StandingsCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandingsCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_way_tie_is_resolved_by_head_to_head_winner(): void
    {
        $tenant = Tenant::factory()->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);

        $alpha = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Alpha']);
        $bravo = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Bravo']);
        $charlie = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Charlie']);
        $delta = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Delta']);

        // Alpha (2-1, -26 diff) vs Bravo (2-1, +58 diff): Alpha won head-to-head 70-68!
        // Charlie (1-2) vs Delta (1-2): Charlie won head-to-head 70-60!
        $results = [
            [$alpha, $bravo, 70, 68],
            [$alpha, $charlie, 72, 70],
            [$alpha, $delta, 60, 90],
            [$bravo, $charlie, 85, 60],
            [$bravo, $delta, 95, 60],
            [$charlie, $delta, 70, 60],
        ];

        foreach ($results as [$home, $away, $homeScore, $awayScore]) {
            Game::factory()->finished($homeScore, $awayScore)->create([
                'tenant_id' => $tenant->id,
                'tournament_id' => $tournament->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
            ]);
        }

        $standings = app(StandingsCalculator::class)->calculate($tournament);

        $this->assertSame(['Alpha', 'Bravo', 'Charlie', 'Delta'], $standings->pluck('team.name')->all());
        $this->assertSame('Head-to-head', $standings[0]['tie_breaker_note']);
    }

    public function test_three_way_circular_tie_is_resolved_by_head_to_head_differential(): void
    {
        $tenant = Tenant::factory()->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);

        $teamA = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 1']);
        $teamB = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 2']);
        $teamC = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 3']);

        // A beats B by 12 (82-70), B beats C by 6 (76-70), C beats A by 2 (72-70)
        // H2H diffs: A = +10, C = -4, B = -6
        Game::factory()->finished(82, 70)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamB->id,
        ]);
        Game::factory()->finished(76, 70)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teamB->id,
            'away_team_id' => $teamC->id,
        ]);
        Game::factory()->finished(72, 70)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teamC->id,
            'away_team_id' => $teamA->id,
        ]);

        $standings = app(StandingsCalculator::class)->calculate($tournament);

        $this->assertSame(['Purok 1', 'Purok 3', 'Purok 2'], $standings->pluck('team.name')->all());
    }

    public function test_tie_without_head_to_head_game_uses_quotient_system(): void
    {
        $tenant = Tenant::factory()->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);

        $teamA = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Kings']);
        $teamB = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Warriors']);
        $teamC = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Titans']);
        $teamD = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Stallions']);

        // Kings beat Titans 80-50 (Quotient = 1.600)
        // Warriors beat Stallions 90-80 (Quotient = 1.125)
        // Scheduled game between Kings and Warriors hasn't been played yet
        Game::factory()->finished(80, 50)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamC->id,
        ]);
        Game::factory()->finished(90, 80)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teamB->id,
            'away_team_id' => $teamD->id,
        ]);
        Game::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamB->id,
            'status' => GameStatus::Scheduled,
        ]);

        $standings = app(StandingsCalculator::class)->calculate($tournament);

        $this->assertSame(['Kings', 'Warriors', 'Stallions', 'Titans'], $standings->pluck('team.name')->all());
        $this->assertEquals(1.600, $standings[0]['quotient']);
        $this->assertEquals(1.125, $standings[1]['quotient']);
    }
}
