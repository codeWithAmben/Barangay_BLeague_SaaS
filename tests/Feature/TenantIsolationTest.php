<?php

namespace Tests\Feature;

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

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_tenant_scope_isolates_queries_between_barangays(): void
    {
        $barangayA = Tenant::factory()->create(['name' => 'Barangay A', 'slug' => 'brgy-a']);
        $barangayB = Tenant::factory()->create(['name' => 'Barangay B', 'slug' => 'brgy-b']);

        $tournamentA = Tournament::factory()->create(['tenant_id' => $barangayA->id, 'name' => 'League A']);
        $tournamentB = Tournament::factory()->create(['tenant_id' => $barangayB->id, 'name' => 'League B']);

        $teamA = Team::factory()->create(['tenant_id' => $barangayA->id, 'tournament_id' => $tournamentA->id, 'name' => 'Team A']);
        $teamB = Team::factory()->create(['tenant_id' => $barangayB->id, 'tournament_id' => $tournamentB->id, 'name' => 'Team B']);

        Player::factory()->create(['tenant_id' => $barangayA->id, 'team_id' => $teamA->id, 'jersey_number' => '7']);
        Player::factory()->create(['tenant_id' => $barangayB->id, 'team_id' => $teamB->id, 'jersey_number' => '7']);

        $context = app(TenantContext::class);
        $context->set($barangayA);

        $this->assertSame(['League A'], Tournament::query()->pluck('name')->all());
        $this->assertSame(['Team A'], Team::query()->pluck('name')->all());
        $this->assertCount(1, Player::query()->get());
        $this->assertNull(Tournament::query()->find($tournamentB->id));

        $context->clear();
    }

    public function test_authenticated_commissioner_only_sees_own_tenant_tournaments_in_livewire(): void
    {
        $barangayA = Tenant::factory()->create(['slug' => 'brgy-alpha']);
        $barangayB = Tenant::factory()->create(['slug' => 'brgy-beta']);

        $commA = User::factory()->forTenant($barangayA)->create();

        Tournament::factory()->create(['tenant_id' => $barangayA->id, 'name' => 'Alpha Cup']);
        Tournament::factory()->create(['tenant_id' => $barangayB->id, 'name' => 'Beta Cup']);

        $this->actingAs($commA);
        app(TenantContext::class)->set($barangayA);

        Livewire::test(ManageTournaments::class)
            ->assertSee('Alpha Cup')
            ->assertDontSee('Beta Cup');
    }

    public function test_scorer_role_is_blocked_from_commissioner_crud_routes(): void
    {
        $tenant = Tenant::factory()->create();
        $scorer = User::factory()->forTenant($tenant)->scorer()->create();

        $this->actingAs($scorer)
            ->get(route('dashboard'))
            ->assertOk();

        $this->actingAs($scorer)
            ->get(route('tournaments.index'))
            ->assertForbidden();

        $this->actingAs($scorer)
            ->get(route('teams.index'))
            ->assertForbidden();

        $this->actingAs($scorer)
            ->get(route('players.index'))
            ->assertForbidden();
    }

    public function test_public_portal_shows_only_requested_barangay_even_when_signed_into_another_tenant(): void
    {
        $barangayA = Tenant::factory()->create(['name' => 'Barangay Alpha', 'slug' => 'brgy-alpha']);
        $barangayB = Tenant::factory()->create(['name' => 'Barangay Beta', 'slug' => 'brgy-beta']);

        $commA = User::factory()->forTenant($barangayA)->create();

        Tournament::factory()->create(['tenant_id' => $barangayA->id, 'name' => 'Alpha Secret Cup']);
        Tournament::factory()->create(['tenant_id' => $barangayB->id, 'name' => 'Beta Public Cup']);

        $this->actingAs($commA)
            ->get(route('portal.show', $barangayB))
            ->assertOk()
            ->assertSee('Beta Public Cup')
            ->assertDontSee('Alpha Secret Cup');
    }
}
