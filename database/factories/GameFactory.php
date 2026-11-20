<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Models\Game;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => fn (array $attributes): int => Tournament::withoutGlobalScopes()
                ->findOrFail($attributes['tournament_id'])
                ->tenant_id,
            'tournament_id' => Tournament::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'scheduled_at' => now()->addDays(2)->setTime(18, 0),
            'venue' => 'Barangay Covered Court',
            'round_number' => 1,
            'status' => GameStatus::Scheduled,
            'home_score' => 0,
            'away_score' => 0,
            'current_quarter' => 1,
        ];
    }

    public function finished(int $homeScore, int $awayScore): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => GameStatus::Finished,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'current_quarter' => 4,
        ]);
    }
}
