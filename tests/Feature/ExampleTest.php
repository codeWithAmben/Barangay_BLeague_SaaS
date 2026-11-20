<?php

namespace Tests\Feature;

use App\Enums\TournamentStatus;
use App\Models\Tenant;
use App\Models\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Barangay San Roque',
            'slug' => 'brgy-san-roque',
            'logo_path' => 'images/logos/brgy-san-roque.svg',
        ]);

        Tournament::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'SK Summer League 2026',
            'status' => TournamentStatus::Registration,
        ]);

        Tournament::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Hidden Draft Cup',
            'status' => TournamentStatus::Draft,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Barangay League')
            ->assertSee('How It Works in 3 Steps')
            ->assertSee('Start Free')
            ->assertSee('Sign In')
            ->assertSee('images/hero-arena.jpg')
            ->assertSee('images/barangay-court.jpg')
            ->assertSee($tenant->name)
            ->assertSee('images/logos/brgy-san-roque.svg')
            ->assertSee('SK Summer League 2026')
            ->assertDontSee('Hidden Draft Cup');
    }

    public function test_public_portal_hides_draft_tournaments(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Barangay Bagong Silang',
            'slug' => 'brgy-bagong-silang',
        ]);

        Tournament::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Published Cup',
            'status' => TournamentStatus::Ongoing,
        ]);

        Tournament::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Draft Cup',
            'status' => TournamentStatus::Draft,
        ]);

        $this->get(route('portal.show', $tenant))
            ->assertOk()
            ->assertSee('Published Cup')
            ->assertDontSee('Draft Cup');
    }
}
