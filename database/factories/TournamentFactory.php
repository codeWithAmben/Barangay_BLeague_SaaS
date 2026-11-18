<?php

namespace Database\Factories;

use App\Enums\TournamentCategory;
use App\Enums\TournamentFormat;
use App\Enums\TournamentStatus;
use App\Models\Tenant;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => 'SK Summer League '.date('Y'),
            'category' => TournamentCategory::Open,
            'format_type' => TournamentFormat::SingleRoundRobin,
            'status' => TournamentStatus::Registration,
            'roster_locked' => false,
            'roster_lock_after_game' => 2,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
        ];
    }
}
