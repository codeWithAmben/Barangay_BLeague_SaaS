<?php

namespace Tests\Feature;

use App\Enums\GameEventType;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Enums\PlayerPosition;
use App\Livewire\Awards\ManageAwards;
use App\Livewire\Billing\ManageBilling;
use App\Models\Game;
use App\Models\PaymentTransaction;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Tournament;
use App\Models\User;
use App\Services\GameScoringService;
use App\Services\SeasonAwardsService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AwardsAndBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_season_awards_computes_mvp_mythical_five_and_bpg_poster(): void
    {
        $tenant = Tenant::factory()->pro()->create(['name' => 'Barangay San Roque', 'slug' => 'brgy-san-roque']);
        $commissioner = User::factory()->forTenant($tenant)->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id, 'name' => 'SK Summer League 2026']);

        $teamA = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 1 Kings']);
        $teamB = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 2 Stallions']);

        $pg = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $teamA->id, 'jersey_number' => '7', 'position' => PlayerPosition::PointGuard, 'first_name' => 'Jericho', 'last_name' => 'Alvarez']);
        $sg = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $teamA->id, 'jersey_number' => '11', 'position' => PlayerPosition::ShootingGuard]);
        $sf = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $teamB->id, 'jersey_number' => '23', 'position' => PlayerPosition::SmallForward]);
        $pf = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $teamB->id, 'jersey_number' => '34', 'position' => PlayerPosition::PowerForward]);
        $c = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $teamA->id, 'jersey_number' => '15', 'position' => PlayerPosition::Center]);

        $game = Game::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $teamA->id,
            'away_team_id' => $teamB->id,
        ]);

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        $scoringService = app(GameScoringService::class);
        $scoringService->ensureRostersInitialized($game);

        // PG dominates: 8 PTS (3+3+2), 2 REB, 3 AST = 13 EFF
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::Point3, 1, '08:00');
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::Point3, 2, '07:00');
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::Point2, 3, '06:00');
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::ReboundDef, 3, '05:00');
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::ReboundOff, 4, '04:00');
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::Assist, 4, '03:00');
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::Assist, 4, '02:00');
        $scoringService->recordEvent($game, $teamA->id, $pg->id, GameEventType::Assist, 4, '01:00');

        // SG scores 2, SF scores 3, PF 1 rebound, C 1 block
        $scoringService->recordEvent($game, $teamA->id, $sg->id, GameEventType::Point2, 1, '04:00');
        $scoringService->recordEvent($game, $teamB->id, $sf->id, GameEventType::Point3, 2, '04:00');
        $scoringService->recordEvent($game, $teamB->id, $pf->id, GameEventType::ReboundDef, 2, '03:00');
        $scoringService->recordEvent($game, $teamA->id, $c->id, GameEventType::Block, 3, '02:00');

        $scoringService->finalizeGame($game->fresh());

        $awards = app(SeasonAwardsService::class)->calculate($tournament);

        $this->assertNotNull($awards['mvp']);
        $this->assertSame($pg->id, $awards['mvp']['player']->id);
        $this->assertEquals(13.0, $awards['mvp']['sp_avg']);
        $this->assertCount(5, $awards['mythical_five']);

        Livewire::test(ManageAwards::class, ['selectedTournamentId' => $tournament->id])
            ->assertSee('Jericho Alvarez')
            ->call('openPoster', $game->id)
            ->assertSet('posterData.player_name', 'Jericho Alvarez')
            ->assertSet('posterData.eff', 13);

        // Public portal & live scoreboard show leaders and BPG poster button
        auth()->logout();
        app(TenantContext::class)->clear();

        $this->get(route('portal.show', $tenant))
            ->assertOk()
            ->assertSee('MVP Race & League Leaders')
            ->assertSee('Jericho Alvarez');

        $this->get(route('live.game', $game))
            ->assertOk()
            ->assertSee('Download BPG Poster');
    }

    public function test_demo_billing_upgrades_trial_tenant_to_barangay_pro(): void
    {
        config(['billing.demo_mode' => true, 'billing.paymongo.secret_key' => null]);

        $tenant = Tenant::factory()->create(['plan_type' => PlanType::Trial]);
        $commissioner = User::factory()->forTenant($tenant)->create();

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        Livewire::test(ManageBilling::class)
            ->set('provider', PaymentProvider::PayMongo->value)
            ->call('subscribe', PlanType::BarangayPro->value)
            ->assertHasNoErrors();

        $tenant->refresh();
        $this->assertSame(PlanType::BarangayPro, $tenant->plan_type);
        $this->assertNotNull($tenant->subscription_ends_at);
        $this->assertDatabaseHas('payment_transactions', [
            'tenant_id' => $tenant->id,
            'plan_type' => PlanType::BarangayPro->value,
            'status' => PaymentStatus::Paid->value,
        ]);
    }

    public function test_paymongo_checkout_and_signed_webhook_activate_municipality_plan(): void
    {
        config([
            'billing.demo_mode' => false,
            'billing.paymongo.secret_key' => 'sk_test_paymongo',
            'billing.paymongo.webhook_secret' => 'whsec_paymongo',
        ]);

        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_pm_12345',
                    'attributes' => [
                        'checkout_url' => 'https://checkout.paymongo.test/cs_pm_12345',
                    ],
                ],
            ], 200),
        ]);

        $tenant = Tenant::factory()->create(['plan_type' => PlanType::Trial]);
        $commissioner = User::factory()->forTenant($tenant)->create();

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        Livewire::test(ManageBilling::class)
            ->set('provider', PaymentProvider::PayMongo->value)
            ->call('subscribe', PlanType::Municipality->value)
            ->assertRedirect('https://checkout.paymongo.test/cs_pm_12345');

        $tx = PaymentTransaction::withoutGlobalScopes()->where('provider_session_id', 'cs_pm_12345')->firstOrFail();
        $this->assertSame(PaymentStatus::Pending, $tx->status);

        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_pm_12345',
                        'attributes' => [
                            'reference_number' => $tx->reference,
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = (string) time();
        $sig = hash_hmac('sha256', "{$timestamp}.{$payload}", 'whsec_paymongo');

        $this->call('POST', route('webhooks.paymongo'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PAYMONGO_SIGNATURE' => "t={$timestamp},te={$sig},li=",
        ], $payload)->assertOk();

        $tenant->refresh();
        $tx->refresh();
        $this->assertSame(PlanType::Municipality, $tenant->plan_type);
        $this->assertSame(PaymentStatus::Paid, $tx->status);
    }

    public function test_stripe_webhook_activates_barangay_pro_plan(): void
    {
        config([
            'billing.stripe.webhook_secret' => 'whsec_stripe',
        ]);

        $tenant = Tenant::factory()->create(['plan_type' => PlanType::Trial]);
        $tx = PaymentTransaction::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'reference' => 'BH-STRIPE-001',
            'provider' => PaymentProvider::Stripe,
            'plan_type' => PlanType::BarangayPro,
            'amount_centavos' => 49900,
            'currency' => 'PHP',
            'status' => PaymentStatus::Pending,
            'provider_session_id' => 'cs_stripe_999',
        ]);

        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_stripe_999',
                    'client_reference_id' => 'BH-STRIPE-001',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $timestamp = (string) time();
        $sig = hash_hmac('sha256', "{$timestamp}.{$payload}", 'whsec_stripe');

        $this->call('POST', route('webhooks.stripe'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$sig}",
        ], $payload)->assertOk();

        $tenant->refresh();
        $tx->refresh();
        $this->assertSame(PlanType::BarangayPro, $tenant->plan_type);
        $this->assertSame(PaymentStatus::Paid, $tx->status);
    }

    public function test_scorer_cannot_access_awards_or_billing_routes(): void
    {
        $tenant = Tenant::factory()->create();
        $scorer = User::factory()->forTenant($tenant)->scorer()->create();

        $this->actingAs($scorer)->get(route('awards.index'))->assertForbidden();
        $this->actingAs($scorer)->get(route('billing.index'))->assertForbidden();
    }
}
