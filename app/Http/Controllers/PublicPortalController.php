<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Tournament;
use Illuminate\View\View;

class PublicPortalController extends Controller
{
    public function home(): View
    {
        $tenants = Tenant::query()
            ->withCount('tournaments')
            ->orderBy('name')
            ->limit(12)
            ->get();

        return view('welcome', [
            'tenants' => $tenants,
        ]);
    }

    public function show(Tenant $tenant): View
    {
        $tournaments = Tournament::query()
            ->with(['teams.players' => fn ($query) => $query->orderBy('jersey_number')])
            ->latest()
            ->get();

        return view('portal.show', [
            'tenant' => $tenant,
            'tournaments' => $tournaments,
        ]);
    }
}
