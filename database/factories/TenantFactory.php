<?php

namespace Database\Factories;

use App\Enums\PlanType;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Barangay '.fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 999),
            'municipality' => fake()->city(),
            'province' => 'Metro Manila',
            'plan_type' => PlanType::Trial,
            'subscription_ends_at' => null,
        ];
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_type' => PlanType::BarangayPro,
            'subscription_ends_at' => now()->addMonth(),
        ]);
    }
}
