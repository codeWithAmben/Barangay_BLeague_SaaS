<?php

use App\Http\Controllers\Auth\BarangayOnboardingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicPortalController;
use App\Livewire\Dashboard;
use App\Livewire\Players\ManagePlayers;
use App\Livewire\Teams\ManageTeams;
use App\Livewire\Tournaments\ManageTournaments;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortalController::class, 'home'])->name('home');

Route::get('/l/{tenant:slug}', [PublicPortalController::class, 'show'])
    ->middleware('tenant.route')
    ->name('portal.show');

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

    Route::middleware('role:commissioner,super_admin')->group(function (): void {
        Route::get('/tournaments', ManageTournaments::class)->name('tournaments.index');
        Route::get('/teams', ManageTeams::class)->name('teams.index');
        Route::get('/players', ManagePlayers::class)->name('players.index');
    });
});
