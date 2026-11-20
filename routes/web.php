<?php

use App\Http\Controllers\Auth\BarangayOnboardingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BillingWebhookController;
use App\Http\Controllers\PublicPortalController;
use App\Livewire\Awards\ManageAwards;
use App\Livewire\Billing\ManageBilling;
use App\Livewire\Dashboard;
use App\Livewire\Players\ManagePlayers;
use App\Livewire\PublicLiveScoreboard;
use App\Livewire\Schedule\ManageSchedule;
use App\Livewire\Scorer\CourtsideScorer;
use App\Livewire\Scorer\ScorerGames;
use App\Livewire\Teams\ManageTeams;
use App\Livewire\Tournaments\ManageTournaments;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortalController::class, 'home'])->name('home');

Route::get('/l/{tenant:slug}', [PublicPortalController::class, 'show'])
    ->middleware('tenant.route')
    ->name('portal.show');

Route::get('/live/{game}', PublicLiveScoreboard::class)->name('live.game');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [BarangayOnboardingController::class, 'create'])->name('register');
    Route::post('/register', [BarangayOnboardingController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'tenant'])->group(function (): void {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/scorer', ScorerGames::class)->name('scorer.index');
    Route::get('/scorer/games/{game}', CourtsideScorer::class)->name('scorer.game');

    Route::middleware('role:commissioner,super_admin')->group(function (): void {
        Route::get('/tournaments', ManageTournaments::class)->name('tournaments.index');
        Route::get('/teams', ManageTeams::class)->name('teams.index');
        Route::get('/players', ManagePlayers::class)->name('players.index');
        Route::get('/schedule', ManageSchedule::class)->name('schedule.index');
        Route::get('/awards', ManageAwards::class)->name('awards.index');
        Route::get('/billing', ManageBilling::class)->name('billing.index');
    });
});

Route::post('/webhooks/paymongo', [BillingWebhookController::class, 'paymongo'])->name('webhooks.paymongo');
Route::post('/webhooks/stripe', [BillingWebhookController::class, 'stripe'])->name('webhooks.stripe');
