<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purok = fake()->numberBetween(1, 12);

        return [
            'tenant_id' => fn (array $attributes): int => Tournament::withoutGlobalScopes()
                ->findOrFail($attributes['tournament_id'])
                ->tenant_id,
            'tournament_id' => Tournament::factory(),
            'name' => "Purok {$purok} ".fake()->unique()->word(),
            'purok_zone' => "Purok {$purok}",
            'coach_name' => fake()->name(),
            'manager_name' => fake()->name(),
            'primary_color' => fake()->hexColor(),
            'secondary_color' => '#1e293b',
        ];
    }
}
