<?php

namespace App\Http\Controllers;

use App\Enums\TournamentStatus;
use App\Models\Tenant;
use App\Models\Tournament;
use App\Services\SeasonAwardsService;
use App\Services\StandingsCalculator;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function home(): View
    {
        $tenants = Tenant::query()
            ->withCount([
                'tournaments' => fn ($query) => $query->where('status', '!=', TournamentStatus::Draft),
            ])
            ->orderBy('name')
            ->limit(12)
            ->get();

        $featuredTournaments = Tournament::withoutGlobalScopes()
            ->where('status', '!=', TournamentStatus::Draft)
            ->with('tenant')
            ->withCount('teams')
            ->latest()
            ->limit(6)
            ->get();

        return view('welcome', [
            'tenants' => $tenants,
            'featuredTournaments' => $featuredTournaments,
        ]);
    }

    public function show(
        Tenant $tenant,
        StandingsCalculator $standingsCalculator,
        SeasonAwardsService $awardsService,
    ): View {
        $tournaments = Tournament::query()
            ->where('status', '!=', TournamentStatus::Draft)
            ->with([
                'teams.players' => fn ($query) => $query->orderBy('jersey_number'),
                'games' => fn ($query) => $query->with(['homeTeam', 'awayTeam', 'bestPlayer'])->orderBy('round_number')->orderBy('scheduled_at'),
            ])
            ->latest()
            ->get();

        $standingsByTournament = [];
        $awardsByTournament = [];

        foreach ($tournaments as $tournament) {
            $standingsByTournament[$tournament->id] = $standingsCalculator->calculate($tournament);
            $awardsByTournament[$tournament->id] = $awardsService->calculate($tournament);
        }

        return view('portal.show', [
            'tenant' => $tenant,
            'tournaments' => $tournaments,
            'standingsByTournament' => $standingsByTournament,
            'awardsByTournament' => $awardsByTournament,
        ]);
    }
}
