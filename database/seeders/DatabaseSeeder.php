<?php

namespace Database\Seeders;

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
        }

        $bagongSilang = Tenant::create([
            'name' => 'Barangay Bagong Silang',
            'slug' => 'brgy-bagong-silang',
            'municipality' => 'Caloocan City',
            'province' => 'Metro Manila',
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
    }
}
