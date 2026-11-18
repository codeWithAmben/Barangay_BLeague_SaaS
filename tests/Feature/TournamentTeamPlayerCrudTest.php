<?php

namespace Tests\Feature;

use App\Enums\PlayerPosition;
use App\Livewire\Players\ManagePlayers;
use App\Livewire\Teams\ManageTeams;
use App\Livewire\Tournaments\ManageTournaments;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Tournament;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TournamentTeamPlayerCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_plan_enforces_one_tournament_and_six_teams_limit(): void
    {
        $tenant = Tenant::factory()->create(); // Trial by default
        $commissioner = User::factory()->forTenant($tenant)->create();

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        Livewire::test(ManageTournaments::class)
            ->call('openCreate')
            ->set('name', 'First SK Cup')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('tournaments', 1);

        // Second tournament blocked by Trial limit
        Livewire::test(ManageTournaments::class)
            ->call('openCreate')
            ->assertSet('showForm', false)
            ->assertSee('allows up to 1 tournament');

        $tournament = Tournament::withoutGlobalScopes()->firstOrFail();

        Team::factory()->count(6)->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
        ]);

        // 7th team blocked by Trial limit
        Livewire::test(ManageTeams::class, ['selectedTournamentId' => $tournament->id])
            ->call('openCreate')
            ->assertSet('showForm', false)
            ->assertSee('allows up to 6 teams');
    }

    public function test_jersey_number_must_be_unique_per_team_but_can_repeat_across_teams(): void
    {
        $tenant = Tenant::factory()->pro()->create();
        $commissioner = User::factory()->forTenant($tenant)->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);
        $team1 = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 1']);
        $team2 = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 2']);

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        // Add #23 to Team 1
        Livewire::test(ManagePlayers::class, ['selectedTeamId' => $team1->id])
            ->call('openCreate')
            ->set('team_id', $team1->id)
            ->set('first_name', 'Jericho')
            ->set('last_name', 'Alvarez')
            ->set('jersey_number', '23')
            ->set('position', PlayerPosition::SmallForward->value)
            ->call('save')
            ->assertHasNoErrors();

        // Duplicate #23 on Team 1 must fail validation
        Livewire::test(ManagePlayers::class, ['selectedTeamId' => $team1->id])
            ->call('openCreate')
            ->set('team_id', $team1->id)
            ->set('first_name', 'Carlo')
            ->set('last_name', 'Reyes')
            ->set('jersey_number', '23')
            ->set('position', PlayerPosition::PointGuard->value)
            ->call('save')
            ->assertHasErrors(['jersey_number' => 'unique']);

        // #23 on Team 2 must succeed
        Livewire::test(ManagePlayers::class, ['selectedTeamId' => $team2->id])
            ->call('openCreate')
            ->set('team_id', $team2->id)
            ->set('first_name', 'Miguel')
            ->set('last_name', 'Santos')
            ->set('jersey_number', '23')
            ->set('position', PlayerPosition::ShootingGuard->value)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, Player::withoutGlobalScopes()->where('jersey_number', '23')->count());
    }

    public function test_roster_lock_prevents_adding_or_removing_players(): void
    {
        $tenant = Tenant::factory()->pro()->create();
        $commissioner = User::factory()->forTenant($tenant)->create();
        $tournament = Tournament::factory()->create([
            'tenant_id' => $tenant->id,
            'roster_locked' => true,
        ]);
        $team = Team::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
        ]);
        $player = Player::factory()->create([
            'tenant_id' => $tenant->id,
            'team_id' => $team->id,
            'jersey_number' => '10',
        ]);

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        Livewire::test(ManagePlayers::class, ['selectedTeamId' => $team->id])
            ->call('openCreate')
            ->assertSet('showForm', false)
            ->assertSee('currently locked')
            ->call('delete', $player->id)
            ->assertSee('is locked');

        $this->assertDatabaseHas('players', ['id' => $player->id]);
    }
}
