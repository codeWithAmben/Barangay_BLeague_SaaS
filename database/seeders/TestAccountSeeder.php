<?php

namespace Database\Seeders;

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountSeeder extends Seeder
{
    /**
     * Create or update a standard test commissioner account.
     */
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrCreate(
            ['slug' => 'brgy-san-roque'],
            [
                'name' => 'Barangay San Roque',
                'municipality' => 'Marikina City',
                'province' => 'Metro Manila',
                'logo_path' => 'images/logos/brgy-san-roque.svg',
                'plan_type' => PlanType::BarangayPro,
                'subscription_ends_at' => now()->addMonths(6),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Test Commissioner',
                'password' => Hash::make('password'),
                'role' => UserRole::Commissioner,
                'email_verified_at' => now(),
            ]
        );
    }
}
