<?php

namespace Database\Seeders;

use App\Enums\GameEventType;
use App\Enums\GameStatus;
use App\Enums\PlanType;
use App\Enums\PlayerPosition;
use App\Enums\TournamentCategory;
use App\Enums\TournamentFormat;
use App\Enums\TournamentStatus;
use App\Enums\UserRole;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tenant;
use App\Models\Tournament;
use App\Models\User;
use App\Services\GameScoringService;
use App\Services\RoundRobinScheduler;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sanRoque = Tenant::create([
            'name' => 'Barangay San Roque',
            'slug' => 'brgy-san-roque',
            'municipality' => 'Marikina City',
            'province' => 'Metro Manila',
            'logo_path' => 'images/logos/brgy-san-roque.svg',
            'plan_type' => PlanType::BarangayPro,
            'subscription_ends_at' => now()->addMonths(6),
        ]);

        User::create([
            'tenant_id' => $sanRoque->id,
            'name' => 'Comm. Marco Reyes',
            'email' => 'commissioner@sanroque.ph',
            'password' => Hash::make('password'),
            'role' => UserRole::Commissioner,
            'email_verified_at' => now(),
        ]);

        User::create([
            'tenant_id' => $sanRoque->id,
            'name' => 'Table Scorer Carlo',
            'email' => 'scorer@sanroque.ph',
            'password' => Hash::make('password'),
            'role' => UserRole::Scorer,
            'email_verified_at' => now(),
        ]);

        $summerLeague = Tournament::create([
            'tenant_id' => $sanRoque->id,
            'name' => 'SK Summer League 2026',
            'category' => TournamentCategory::Senior,
            'format_type' => TournamentFormat::SingleRoundRobin,
            'status' => TournamentStatus::Registration,
            'roster_locked' => false,
            'roster_lock_after_game' => 2,
            'start_date' => '2026-04-15',
            'end_date' => '2026-06-20',
        ]);

        $teamsData = [
            ['name' => 'Purok 1 Kings', 'purok_zone' => 'Purok 1', 'coach_name' => 'Coach Jun Bautista', 'primary_color' => '#ea580c', 'secondary_color' => '#0f172a'],
            ['name' => 'Purok 2 Stallions', 'purok_zone' => 'Purok 2', 'coach_name' => 'Coach Allan Santos', 'primary_color' => '#2563eb', 'secondary_color' => '#f8fafc'],
            ['name' => 'Purok 3 Warriors', 'purok_zone' => 'Purok 3', 'coach_name' => 'Coach Rico Mendoza', 'primary_color' => '#16a34a', 'secondary_color' => '#0f172a'],
            ['name' => 'Purok 4 Titans', 'purok_zone' => 'Purok 4', 'coach_name' => 'Coach Bong Dela Cruz', 'primary_color' => '#dc2626', 'secondary_color' => '#1e293b'],
        ];

        $samplePlayers = [
            ['first_name' => 'Jericho', 'last_name' => 'Alvarez', 'jersey_number' => '7', 'position' => PlayerPosition::PointGuard, 'is_import' => false],
            ['first_name' => 'Kiefer', 'last_name' => 'Navarro', 'jersey_number' => '11', 'position' => PlayerPosition::ShootingGuard, 'is_import' => false],
            ['first_name' => 'Gabriel', 'last_name' => 'Villanueva', 'jersey_number' => '23', 'position' => PlayerPosition::SmallForward, 'is_import' => false],
            ['first_name' => 'Christian', 'last_name' => 'Tolentino', 'jersey_number' => '34', 'position' => PlayerPosition::PowerForward, 'is_import' => true],
            ['first_name' => 'Mark', 'last_name' => 'Pangilinan', 'jersey_number' => '15', 'position' => PlayerPosition::Center, 'is_import' => false],
        ];

        $team4Players = [
            ['first_name' => 'Nico', 'last_name' => 'Salvador', 'jersey_number' => '4', 'position' => PlayerPosition::PointGuard, 'is_import' => false],
            ['first_name' => 'Paolo', 'last_name' => 'Gomez', 'jersey_number' => '9', 'position' => PlayerPosition::ShootingGuard, 'is_import' => false],
            ['first_name' => 'Bryan', 'last_name' => 'Cruz', 'jersey_number' => '14', 'position' => PlayerPosition::SmallForward, 'is_import' => false],
            ['first_name' => 'Kevin', 'last_name' => 'Lim', 'jersey_number' => '21', 'position' => PlayerPosition::PowerForward, 'is_import' => true],
            ['first_name' => 'Rafael', 'last_name' => 'Diaz', 'jersey_number' => '33', 'position' => PlayerPosition::Center, 'is_import' => false],
        ];

        foreach ($teamsData as $index => $teamData) {
            $team = Team::create([
                'tenant_id' => $sanRoque->id,
                'tournament_id' => $summerLeague->id,
                ...$teamData,
            ]);

            if ($index === 0) {
                foreach ($samplePlayers as $playerData) {
                    Player::create([
                        'tenant_id' => $sanRoque->id,
                        'team_id' => $team->id,
                        'birthdate' => '2001-05-12',
                        ...$playerData,
                    ]);
                }
            }

            if ($index === 3) {
                foreach ($team4Players as $playerData) {
                    Player::create([
                        'tenant_id' => $sanRoque->id,
                        'team_id' => $team->id,
                        'birthdate' => '2000-08-20',
                        ...$playerData,
                    ]);
                }
            }
        }

        $games = app(RoundRobinScheduler::class)->generateForTournament($summerLeague, [
            'venue' => 'Brgy. San Roque Covered Court',
            'start_date' => '2026-04-15',
            'first_tipoff' => '18:00',
            'games_per_day' => 2,
        ]);

        if ($games->count() >= 2) {
            $scoringService = app(GameScoringService::class);
            $game1 = $games[0];
            $scoringService->ensureRostersInitialized($game1);

            $homePlayers = Player::withoutGlobalScopes()->where('team_id', $game1->home_team_id)->get()->keyBy('jersey_number');
            $awayPlayers = Player::withoutGlobalScopes()->where('team_id', $game1->away_team_id)->get()->keyBy('jersey_number');

            if (isset($homePlayers['7'], $homePlayers['23'], $awayPlayers['4'])) {
                $star = $homePlayers['7'];
                $forward = $homePlayers['23'];
                $awayGuard = $awayPlayers['4'];

                // Seed play-by-play stats for Game 1 (Purok 1 Kings vs Purok 4 Titans)
                foreach ([
                    [$game1->home_team_id, $star->id, GameEventType::Point3, 1, '08:40'],
                    [$game1->home_team_id, $star->id, GameEventType::Point2, 2, '06:15'],
                    [$game1->home_team_id, $star->id, GameEventType::Point3, 3, '04:20'],
                    [$game1->home_team_id, $star->id, GameEventType::Point2, 4, '02:05'],
                    [$game1->home_team_id, $star->id, GameEventType::Assist, 1, '07:10'],
                    [$game1->home_team_id, $star->id, GameEventType::Assist, 2, '05:00'],
                    [$game1->home_team_id, $star->id, GameEventType::ReboundDef, 3, '03:50'],
                    [$game1->home_team_id, $star->id, GameEventType::Steal, 4, '01:20'],
                    [$game1->home_team_id, $forward->id, GameEventType::Point2, 1, '07:05'],
                    [$game1->home_team_id, $forward->id, GameEventType::ReboundOff, 2, '04:45'],
                    [$game1->away_team_id, $awayGuard->id, GameEventType::Point3, 1, '08:12'],
                    [$game1->away_team_id, $awayGuard->id, GameEventType::Point2, 2, '05:30'],
                    [$game1->away_team_id, $awayGuard->id, GameEventType::Assist, 3, '06:00'],
                ] as [$tid, $pid, $etype, $q, $clk]) {
                    $scoringService->recordEvent($game1, $tid, $pid, $etype, $q, $clk);
                }

                $scoringService->finalizeGame($game1->fresh(), $star->id);
            }

            $games[1]->update([
                'status' => GameStatus::Finished,
                'home_score' => 69,
                'away_score' => 72,
                'current_quarter' => 4,
            ]);
        }

        $bagongSilang = Tenant::create([
            'name' => 'Barangay Bagong Silang',
            'slug' => 'brgy-bagong-silang',
            'municipality' => 'Caloocan City',
            'province' => 'Metro Manila',
            'logo_path' => 'images/logos/brgy-bagong-silang.svg',
            'plan_type' => PlanType::Trial,
        ]);

        User::create([
            'tenant_id' => $bagongSilang->id,
            'name' => 'Comm. Elena Ramos',
            'email' => 'commissioner@bagongsilang.ph',
            'password' => Hash::make('password'),
            'role' => UserRole::Commissioner,
            'email_verified_at' => now(),
        ]);

        Tournament::create([
            'tenant_id' => $bagongSilang->id,
            'name' => 'Inter-Purok Cup 2026',
            'category' => TournamentCategory::Open,
            'format_type' => TournamentFormat::GroupKnockout,
            'status' => TournamentStatus::Draft,
        ]);

        $this->call(TestAccountSeeder::class);
    }
}
