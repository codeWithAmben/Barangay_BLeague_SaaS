<?php

namespace App\Http\Controllers\Auth;

use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class BarangayOnboardingController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'barangay_name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:80',
                'alpha_dash',
                Rule::unique('tenants', 'slug'),
            ],
            'municipality' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $slug = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : Str::slug($validated['barangay_name']);

        if (Tenant::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.Str::lower(Str::random(4));
        }

        $user = DB::transaction(function () use ($validated, $slug): User {
            $tenant = Tenant::create([
                'name' => $validated['barangay_name'],
                'slug' => $slug,
                'municipality' => $validated['municipality'] ?? null,
                'province' => $validated['province'] ?? null,
                'plan_type' => PlanType::Trial,
            ]);

            return User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::Commissioner,
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', "Welcome! {$user->tenant->name} league portal is ready.");
    }
}
