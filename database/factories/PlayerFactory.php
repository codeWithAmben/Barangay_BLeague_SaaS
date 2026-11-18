<?php

namespace Database\Factories;

use App\Enums\PlayerPosition;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => fn (array $attributes): int => Team::withoutGlobalScopes()
                ->findOrFail($attributes['team_id'])
                ->tenant_id,
            'team_id' => Team::factory(),
            'first_name' => fake()->firstNameMale(),
            'last_name' => fake()->lastName(),
            'jersey_number' => (string) fake()->unique()->numberBetween(0, 99),
            'position' => fake()->randomElement(PlayerPosition::cases()),
            'birthdate' => fake()->dateTimeBetween('-30 years', '-16 years')->format('Y-m-d'),
            'is_import' => false,
            'is_suspended' => false,
        ];
    }
}
