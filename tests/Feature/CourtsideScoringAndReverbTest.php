<?php

namespace Tests\Feature;

use App\Enums\GameEventType;
use App\Enums\GameStatus;
use App\Events\GameUpdated;
use App\Livewire\PublicLiveScoreboard;
use App\Livewire\Scorer\CourtsideScorer;
use App\Models\Game;
use App\Models\GameRoster;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Tournament;
use App\Models\User;
use App\Services\GameScoringService;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourtsideScoringAndReverbTest extends TestCase
{
    use RefreshDatabase;

    public function test_scorer_can_log_plays_undo_and_auto_foul_out_on_fifth_foul(): void
    {
        $tenant = Tenant::factory()->pro()->create();
        $scorer = User::factory()->forTenant($tenant)->scorer()->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);

        $home = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 1']);
        $away = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 2']);

        $homeStarter = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '7']);
        $homeBench = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '99']);
        // Fill 4 more starters so jersey #99 starts on the bench
        foreach (['1', '2', '3', '4'] as $num) {
            Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => $num]);
        }

        $awayStarter = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $away->id, 'jersey_number' => '10']);

        $game = Game::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);

        $this->actingAs($scorer);
        app(TenantContext::class)->set($tenant);

        $component = Livewire::test(CourtsideScorer::class, ['game' => $game])
            ->call('startGame')
            ->call('selectPlayer', $home->id, $homeStarter->id)
            ->call('logPlay', GameEventType::Point3->value, '09:30')
            ->call('logPlay', GameEventType::Point2->value, '08:45')
            ->call('selectPlayer', $away->id, $awayStarter->id)
            ->call('logPlay', GameEventType::Point1->value, '08:10');

        $game->refresh();
        $this->assertSame(GameStatus::Live, $game->status);
        $this->assertSame(5, $game->home_score);
        $this->assertSame(1, $game->away_score);

        // Undo the away +1 FT
        $component->call('undoLastPlay');
        $game->refresh();
        $this->assertSame(0, $game->away_score);

        // 5 personal fouls ejects the player and takes them off the court
        $component->call('selectPlayer', $home->id, $homeStarter->id);
        for ($i = 0; $i < 5; $i++) {
            $component->call('logPlay', GameEventType::FoulPersonal->value, '07:00');
        }

        $roster = GameRoster::withoutGlobalScopes()
            ->where('game_id', $game->id)
            ->where('player_id', $homeStarter->id)
            ->firstOrFail();

        $this->assertSame(5, $roster->fouls_count);
        $this->assertTrue($roster->is_ejected);
        $this->assertFalse($roster->is_on_court);

        // Undo 5th foul restores eligibility
        $component->call('undoLastPlay');
        $roster->refresh();
        $this->assertSame(4, $roster->fouls_count);
        $this->assertFalse($roster->is_ejected);
        $this->assertTrue($roster->is_on_court);

        // Substitute homeStarter OUT and homeBench IN
        $component->call('selectPlayer', $home->id, $homeStarter->id)
            ->call('substitute', $home->id, $homeBench->id, '06:30')
            ->assertHasNoErrors();

        $this->assertFalse(
            GameRoster::withoutGlobalScopes()->where('game_id', $game->id)->where('player_id', $homeStarter->id)->firstOrFail()->is_on_court
        );
        $this->assertTrue(
            GameRoster::withoutGlobalScopes()->where('game_id', $game->id)->where('player_id', $homeBench->id)->firstOrFail()->is_on_court
        );
    }

    public function test_two_technical_fouls_suspend_player_and_bpg_uses_efficiency_formula(): void
    {
        $tenant = Tenant::factory()->pro()->create();
        $commissioner = User::factory()->forTenant($tenant)->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);
        $home = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id]);
        $away = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id]);

        $star = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '7']);
        $rolePlayer = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '15']);
        $awayPlayer = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $away->id, 'jersey_number' => '23']);

        $game = Game::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);

        $this->actingAs($commissioner);
        app(TenantContext::class)->set($tenant);

        $service = app(GameScoringService::class);
        // Star: 3+2+2 = 7 PTS, 2 REB, 3 AST = 12 EFF
        $service->recordEvent($game, $home->id, $star->id, GameEventType::Point3, 1, '08:00');
        $service->recordEvent($game, $home->id, $star->id, GameEventType::Point2, 1, '07:00');
        $service->recordEvent($game, $home->id, $star->id, GameEventType::Point2, 2, '06:00');
        $service->recordEvent($game, $home->id, $star->id, GameEventType::ReboundDef, 2, '05:30');
        $service->recordEvent($game, $home->id, $star->id, GameEventType::ReboundOff, 2, '05:10');
        $service->recordEvent($game, $home->id, $star->id, GameEventType::Assist, 3, '04:00');
        $service->recordEvent($game, $home->id, $star->id, GameEventType::Assist, 3, '03:30');
        $service->recordEvent($game, $home->id, $star->id, GameEventType::Assist, 4, '02:00');

        // Role player: 2 PTS, 2 TO, 2 PF = -2 EFF
        $service->recordEvent($game, $home->id, $rolePlayer->id, GameEventType::Point2, 1, '05:00');
        $service->recordEvent($game, $home->id, $rolePlayer->id, GameEventType::Turnover, 2, '04:00');
        $service->recordEvent($game, $home->id, $rolePlayer->id, GameEventType::Turnover, 2, '03:00');

        // 2 Technical Fouls on awayPlayer triggers tournament suspension
        $service->recordEvent($game, $away->id, $awayPlayer->id, GameEventType::FoulTechnical, 2, '01:00');
        $service->recordEvent($game, $away->id, $awayPlayer->id, GameEventType::FoulTechnical, 3, '09:00');

        $awayPlayer->refresh();
        $this->assertTrue($awayPlayer->is_suspended);

        $finalized = $service->finalizeGame($game->fresh());
        $this->assertSame(GameStatus::Finished, $finalized->status);
        $this->assertSame($star->id, $finalized->best_player_id);
    }

    public function test_offline_queue_batch_sync_and_public_live_scoreboard(): void
    {
        $tenant = Tenant::factory()->pro()->create(['name' => 'Barangay San Roque']);
        $scorer = User::factory()->forTenant($tenant)->scorer()->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);
        $home = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Kings']);
        $away = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Titans']);
        $player = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '8']);

        $game = Game::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);

        $this->actingAs($scorer);
        app(TenantContext::class)->set($tenant);

        Livewire::test(CourtsideScorer::class, ['game' => $game])
            ->call('flushOfflineQueue', [
                ['teamId' => $home->id, 'playerId' => $player->id, 'eventType' => 'pt_3', 'quarter' => 1, 'gameClock' => '09:10'],
                ['teamId' => $home->id, 'playerId' => $player->id, 'eventType' => 'ast', 'quarter' => 1, 'gameClock' => '08:55'],
            ])
            ->assertHasNoErrors();

        $game->refresh();
        $this->assertSame(3, $game->home_score);

        // Broadcast payload verification
        $event = new GameUpdated($game, app(GameScoringService::class)->computePeriodSummary($game));
        $this->assertSame('game.'.$game->id, $event->broadcastOn()[0]->name);
        $this->assertSame('game.updated', $event->broadcastAs());
        $this->assertSame(3, $event->broadcastWith()['home_score']);

        // Public spectator route works without auth
        auth()->logout();
        app(TenantContext::class)->clear();

        $this->get(route('live.game', $game))
            ->assertOk()
            ->assertSee('Kings')
            ->assertSee('Titans')
            ->assertSee('Live Play-by-Play');

        Livewire::test(PublicLiveScoreboard::class, ['game' => $game])
            ->assertSee('#8')
            ->assertSee('+3 FG Made');
    }

    public function test_scorer_cannot_access_other_tenants_game(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();
        $scorerA = User::factory()->forTenant($tenantA)->scorer()->create();

        $tournamentB = Tournament::factory()->create(['tenant_id' => $tenantB->id]);
        $homeB = Team::factory()->create(['tenant_id' => $tenantB->id, 'tournament_id' => $tournamentB->id]);
        $awayB = Team::factory()->create(['tenant_id' => $tenantB->id, 'tournament_id' => $tournamentB->id]);
        $gameB = Game::factory()->create([
            'tenant_id' => $tenantB->id,
            'tournament_id' => $tournamentB->id,
            'home_team_id' => $homeB->id,
            'away_team_id' => $awayB->id,
        ]);

        $this->actingAs($scorerA)
            ->get(route('scorer.game', $gameB))
            ->assertNotFound();
    }

    public function test_stat_first_flow_split_misses_two_tap_sub_and_end_game_modal(): void
    {
        $tenant = Tenant::factory()->pro()->create();
        $scorer = User::factory()->forTenant($tenant)->scorer()->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);
        $home = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 1']);
        $away = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 2']);

        $p1 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '7']);
        $bench = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '99']);
        foreach (['1', '2', '3', '4'] as $num) {
            Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => $num]);
        }
        Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $away->id, 'jersey_number' => '10']);

        $game = Game::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);

        $this->actingAs($scorer);
        app(TenantContext::class)->set($tenant);

        $this->get(route('scorer.game', $game))
            ->assertOk()
            ->assertSee('Exit Scorer')
            ->assertSee('2PT Miss')
            ->assertSee('3PT Miss')
            ->assertSee('Foul (F)');

        Livewire::test(CourtsideScorer::class, ['game' => $game])
            ->call('logPlayForPlayer', $home->id, $p1->id, GameEventType::Point3->value, '09:20')
            ->call('logPlayForPlayer', $home->id, $p1->id, GameEventType::Missed3->value, '08:50')
            ->call('logPlayForPlayer', $home->id, $p1->id, GameEventType::Missed2->value, '08:10')
            ->call('logPlayForPlayer', $home->id, $p1->id, GameEventType::Missed1->value, '07:40')
            ->call('swapPlayers', $home->id, $p1->id, $bench->id, '07:00')
            ->call('openEndGameModal')
            ->assertSet('showEndGameModal', true)
            ->call('finalizeGame')
            ->assertSet('showEndGameModal', false);

        $game->refresh();
        $this->assertSame(GameStatus::Finished, $game->status);
        $this->assertSame(3, $game->home_score);
        $this->assertSame($p1->id, $game->best_player_id);
    }

    public function test_substitution_undo_blocks_invalid_subs_and_supports_batch_subs(): void
    {
        $tenant = Tenant::factory()->pro()->create();
        $scorer = User::factory()->forTenant($tenant)->scorer()->create();
        $tournament = Tournament::factory()->create(['tenant_id' => $tenant->id]);
        $home = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 1']);
        $away = Team::factory()->create(['tenant_id' => $tenant->id, 'tournament_id' => $tournament->id, 'name' => 'Purok 2']);

        $s1 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '1']);
        $s2 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '2']);
        $s3 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '3']);
        $s4 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '4']);
        $s5 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '5']);
        $b1 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '11']);
        $b2 = Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $home->id, 'jersey_number' => '12']);
        Player::factory()->create(['tenant_id' => $tenant->id, 'team_id' => $away->id, 'jersey_number' => '10']);

        $game = Game::factory()->create([
            'tenant_id' => $tenant->id,
            'tournament_id' => $tournament->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
        ]);

        $this->actingAs($scorer);
        app(TenantContext::class)->set($tenant);

        $component = Livewire::test(CourtsideScorer::class, ['game' => $game])
            ->assertSee('On Court (5/5)')
            ->assertSee('Bench (2)')
            ->assertSee('Starter');

        // Sub s1 out for b1, then single Undo (Z) restores s1 on court and b1 to bench
        $component->call('swapPlayers', $home->id, $s1->id, $b1->id, '06:30');
        $this->assertFalse(GameRoster::withoutGlobalScopes()->where('game_id', $game->id)->where('player_id', $s1->id)->firstOrFail()->is_on_court);
        $this->assertTrue(GameRoster::withoutGlobalScopes()->where('game_id', $game->id)->where('player_id', $b1->id)->firstOrFail()->is_on_court);

        $component->call('undoLastPlay');
        $this->assertTrue(GameRoster::withoutGlobalScopes()->where('game_id', $game->id)->where('player_id', $s1->id)->firstOrFail()->is_on_court);
        $this->assertFalse(GameRoster::withoutGlobalScopes()->where('game_id', $game->id)->where('player_id', $b1->id)->firstOrFail()->is_on_court);

        // Invalid sub: trying to sub in an already-on-court player (s2)
        $component->call('swapPlayers', $home->id, $s1->id, $s2->id, '06:00')
            ->assertSee('already on the court');

        // Batch sub 2 players at once (s1, s2 out -> b1, b2 in)
        $component->call('swapBatch', $home->id, [$s1->id, $s2->id], [$b1->id, $b2->id], '05:00')
            ->assertSee('In Q1 05:00')
            ->assertSee('Out Q1 05:00');

        $onCourtCount = GameRoster::withoutGlobalScopes()
            ->where('game_id', $game->id)
            ->where('team_id', $home->id)
            ->where('is_on_court', true)
            ->count();

        $this->assertSame(5, $onCourtCount);
    }
}
