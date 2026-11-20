<?php

namespace Tests\Feature;

use App\Enums\GameStatus;
use App\Enums\TournamentFormat;
use App\Enums\TournamentStatus;
use App\Livewire\Schedule\ManageSchedule;
use App\Models\Game;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Tournament;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleAndStandingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_commissioner_can_generate_round_robin_schedule_and_record_scores(): void
    {
        $tenant = Tenant::factory()->pro()->create(['slug' => 'brgy-schedule-test']);
        $commissioner = User::factory()->forTenant($tenant)->create();
        $tournament = Tournament::factory()->create([
            'tenant_id' => $tenant->id,
            'format_type' => TournamentFormat::SingleRoundRobin,
            'status' => TournamentStatus::Ongoing,
        ]);

        Team::factory()->count(4)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
        ]);

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        Livewire::test(ManageSchedule::class, ['selectedTournamentId' => $tournament->id])
            ->call('toggleGenerator')
            ->set('venue', 'San Roque Covered Court')
            ->set('start_date', '2026-05-01')
            ->set('first_tipoff', '18:00')
            ->set('games_per_day', 2)
            ->call('generateSchedule')
            ->assertHasNoErrors();

        $this->assertSame(6, Game::withoutGlobalScopes()->where('tournament_id', $tournament->id)->count());

        $firstGame = Game::withoutGlobalScopes()->where('tournament_id', $tournament->id)->firstOrFail();

        // Tied score when marked finished must fail validation
        Livewire::test(ManageSchedule::class, ['selectedTournamentId' => $tournament->id])
            ->call('openScoreEditor', $firstGame->id)
            ->set('homeScore', 75)
            ->set('awayScore', 75)
            ->set('gameStatus', GameStatus::Finished->value)
            ->call('saveScore')
            ->assertHasErrors(['awayScore']);

        // Valid final score updates game and standings
        Livewire::test(ManageSchedule::class, ['selectedTournamentId' => $tournament->id])
            ->call('openScoreEditor', $firstGame->id)
            ->set('homeScore', 82)
            ->set('awayScore', 74)
            ->set('gameStatus', GameStatus::Finished->value)
            ->call('saveScore')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('games', [
            'id' => $firstGame->id,
            'home_score' => 82,
            'away_score' => 74,
            'status' => GameStatus::Finished->value,
        ]);

        // Public portal displays standings & schedule
        $this->get(route('portal.show', $tenant))
            ->assertOk()
            ->assertSee('Team Standings')
            ->assertSee('82 - 74');
    }

    public function test_regenerating_schedule_preserves_finished_games(): void
    {
        $tenant = Tenant::factory()->pro()->create();
        $commissioner = User::factory()->forTenant($tenant)->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);
        $teams = Team::factory()->count(3)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
        ]);

        $finishedGame = Game::factory()->finished(70, 65)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teams[0]->id,
            'away_team_id' => $teams[1]->id,
        ]);

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        Livewire::test(ManageSchedule::class, ['selectedTournamentId' => $tournament->id])
            ->set('replace_scheduled', true)
            ->call('generateSchedule')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('games', ['id' => $finishedGame->id, 'status' => GameStatus::Finished->value]);
    }

    public function test_scorer_cannot_access_schedule_generator(): void
    {
        $tenant = Tenant::factory()->create();
        $scorer = User::factory()->forTenant($tenant)->scorer()->create();

        $this->actingAs($scorer)
            ->get(route('schedule.index'))
            ->assertForbidden();
    }
}
