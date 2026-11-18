<?php

namespace Tests\Feature;

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BarangayOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_barangay_registration_creates_tenant_and_commissioner(): void
    {
        $response = $this->post(route('register.store'), [
            'barangay_name' => 'Barangay Sta. Elena',
            'slug' => 'brgy-sta-elena',
            'municipality' => 'Marikina City',
            'province' => 'Metro Manila',
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@staelena.ph',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $tenant = Tenant::where('slug', 'brgy-sta-elena')->firstOrFail();
        $this->assertSame('Barangay Sta. Elena', $tenant->name);
        $this->assertSame(PlanType::Trial, $tenant->plan_type);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@staelena.ph',
            'tenant_id' => $tenant->id,
            'role' => UserRole::Commissioner->value,
        ]);
    }

    public function test_user_can_login_and_logout(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->forTenant($tenant)->create([
            'email' => 'comm@test.ph',
        ]);

        $loginResponse = $this->post(route('login.store'), [
            'email' => 'comm@test.ph',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
