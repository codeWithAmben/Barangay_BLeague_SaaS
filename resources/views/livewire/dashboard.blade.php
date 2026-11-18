<div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $tenant?->name ?? 'League Dashboard' }}</h1>
            <p class="mt-1 text-sm text-slate-400">
                @if ($tenant?->municipality || $tenant?->province)
                    {{ collect([$tenant->municipality, $tenant->province])->filter()->implode(', ') }} &bull;
                @endif
                Public portal:
                @if ($tenant)
                    <a href="{{ route('portal.show', $tenant) }}" target="_blank" class="text-orange-400 hover:text-orange-300 underline">
                        /l/{{ $tenant->slug }}
                    </a>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('tournaments.index') }}" wire:navigate
               class="rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-500">
                Manage Tournaments
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tournaments</div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-white">{{ $tournamentCount }}</span>
                @if ($tenant?->plan_type->maxTournaments() !== null)
                    <span class="text-xs text-slate-400">/ {{ $tenant->plan_type->maxTournaments() }} (Trial limit)</span>
                @else
                    <span class="text-xs text-emerald-400">Unlimited</span>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Registered Teams</div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-white">{{ $teamCount }}</span>
                @if ($tenant?->plan_type->maxTeamsPerTournament() !== null)
                    <span class="text-xs text-slate-400">Max {{ $tenant->plan_type->maxTeamsPerTournament() }} / tournament</span>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rostered Players</div>
            <div class="mt-2 text-3xl font-bold text-white">{{ $playerCount }}</div>
        </div>
    </div>

    <!-- Tournaments Overview -->
    <div class="rounded-2xl border border-slate-800 bg-slate-900/80 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-base font-semibold text-white">Active &amp; Upcoming Tournaments</h2>
            <a href="{{ route('tournaments.index') }}" wire:navigate class="text-xs font-medium text-orange-400 hover:text-orange-300">
                View all →
            </a>
        </div>

        @if ($tournaments->isEmpty())
            <div class="p-8 text-center text-sm text-slate-400">
                No tournaments yet. Head to <a href="{{ route('tournaments.index') }}" wire:navigate class="text-orange-400 underline">Tournaments</a> to launch your first Inter-Purok or SK League.
            </div>
        @else
            <div class="divide-y divide-slate-800">
                @foreach ($tournaments as $tournament)
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <span class="font-semibold text-white">{{ $tournament->name }}</span>
                                <span class="rounded-full bg-slate-800 px-2.5 py-0.5 text-xs text-slate-300">
                                    {{ $tournament->category->label() }}
                                </span>
                                <span class="rounded-full bg-orange-500/15 px-2.5 py-0.5 text-xs text-orange-300">
                                    {{ $tournament->status->label() }}
                                </span>
                                @if ($tournament->roster_locked)
                                    <span class="rounded-full bg-rose-500/15 px-2.5 py-0.5 text-xs text-rose-300">
                                        Roster Locked
                                    </span>
                                @endif
                            </div>
                            <div class="mt-1 text-xs text-slate-400">
                                {{ $tournament->format_type->label() }} &bull;
                                {{ $tournament->teams_count }} teams &bull;
                                {{ $tournament->players_count }} players
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('teams.index', ['tournament' => $tournament->id]) }}" wire:navigate
                               class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700">
                                Teams ({{ $tournament->teams_count }})
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
