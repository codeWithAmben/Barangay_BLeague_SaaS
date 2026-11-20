<div class="space-y-8">
    <!-- Command Center Header -->
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full border border-orange-500/35 bg-orange-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-orange-300">
                <span class="h-1.5 w-1.5 rounded-full bg-orange-400 animate-pulse"></span>
                Commissioner Command Center
            </div>

            <div class="flex items-center gap-3">
                <span class="w-1.5 h-8 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_12px_rgba(249,115,22,0.65)] shrink-0"></span>
                <h1 class="font-display text-3xl sm:text-5xl font-extrabold italic uppercase tracking-tight bg-gradient-to-r from-white via-slate-100 to-orange-300 bg-clip-text text-transparent">
                    {{ $tenant?->name ?? 'League Dashboard' }}
                </h1>
            </div>

            <p class="text-sm text-slate-300">
                @if ($tenant?->municipality || $tenant?->province)
                    {{ collect([$tenant->municipality, $tenant->province])->filter()->implode(', ') }} &bull;
                @endif
                Public portal:
                @if ($tenant)
                    <a href="{{ route('portal.show', $tenant) }}" target="_blank" class="text-orange-400 hover:text-orange-300 underline font-medium">
                        /l/{{ $tenant->slug }}
                    </a>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('tournaments.index') }}" wire:navigate
               class="btn-hoops-primary inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold text-white">
                <span>Manage Tournaments</span>
                <span aria-hidden="true">&rsaquo;</span>
            </a>
        </div>
    </div>

    <!-- Chamfered KPI Stats Cards -->
    @php
        $maxTournaments = $tenant?->plan_type->maxTournaments();
        $tournamentProgress = $maxTournaments ? min(100, (int) round(($tournamentCount / $maxTournaments) * 100)) : ($tournamentCount > 0 ? 78 : 12);
        $teamProgress = $teamCount > 0 ? min(100, max(20, $teamCount * 12)) : 10;
        $playerProgress = $playerCount > 0 ? min(100, max(22, $playerCount * 5)) : 10;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="arena-card arena-chamfer p-6 flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Tournaments</span>
                    <span class="h-2 w-2 rounded-full bg-orange-400 shadow-[0_0_8px_rgba(251,146,60,0.8)]"></span>
                </div>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="font-display text-4xl sm:text-5xl font-extrabold italic tracking-tight text-white leading-none">
                        {{ $tournamentCount }}
                    </span>
                    @if ($maxTournaments !== null)
                        <span class="text-xs font-medium text-slate-400">/ {{ $maxTournaments }} (Trial limit)</span>
                    @else
                        <span class="rounded-full bg-emerald-500/15 border border-emerald-500/30 px-2 py-0.5 text-[11px] font-semibold text-emerald-300">Unlimited</span>
                    @endif
                </div>
            </div>
            <div class="h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-[#ff5100] to-amber-400" style="width: {{ $tournamentProgress }}%"></div>
            </div>
        </div>

        <div class="arena-card arena-chamfer p-6 flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Registered Teams</span>
                    <span class="h-2 w-2 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.8)]"></span>
                </div>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="font-display text-4xl sm:text-5xl font-extrabold italic tracking-tight text-white leading-none">
                        {{ $teamCount }}
                    </span>
                    @if ($tenant?->plan_type->maxTeamsPerTournament() !== null)
                        <span class="text-xs font-medium text-slate-400">Max {{ $tenant->plan_type->maxTeamsPerTournament() }} / tournament</span>
                    @else
                        <span class="text-xs font-medium text-slate-400">Purok squads</span>
                    @endif
                </div>
            </div>
            <div class="h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-[#ff5100] to-amber-400" style="width: {{ $teamProgress }}%"></div>
            </div>
        </div>

        <div class="arena-card arena-chamfer p-6 flex flex-col justify-between gap-4">
            <div>
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Rostered Players</span>
                    <span class="h-2 w-2 rounded-full bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.8)]"></span>
                </div>
                <div class="mt-3 flex items-baseline gap-2.5">
                    <span class="font-display text-4xl sm:text-5xl font-extrabold italic tracking-tight text-white leading-none">
                        {{ $playerCount }}
                    </span>
                    <span class="text-xs font-medium text-slate-400">Verified jerseys</span>
                </div>
            </div>
            <div class="h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-[#ff5100] to-amber-400" style="width: {{ $playerProgress }}%"></div>
            </div>
        </div>
    </div>

    <!-- Tournaments Overview -->
    <div class="arena-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-white">Active &amp; Upcoming Tournaments</h2>
            </div>
            <a href="{{ route('tournaments.index') }}" wire:navigate class="text-xs font-semibold text-orange-400 hover:text-orange-300 transition">
                View all &rarr;
            </a>
        </div>

        @if ($tournaments->isEmpty())
            <div class="p-8 text-center text-sm text-slate-300">
                No tournaments yet. Head to <a href="{{ route('tournaments.index') }}" wire:navigate class="text-orange-400 underline font-semibold">Tournaments</a> to launch your first Inter-Purok or SK League.
            </div>
        @else
            <div class="divide-y divide-white/10">
                @foreach ($tournaments as $tournament)
                    @php
                        $isLiveRegistration = in_array($tournament->status, [\App\Enums\TournamentStatus::Registration, \App\Enums\TournamentStatus::Ongoing], true);
                    @endphp
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 hover:bg-white/[0.03] transition">
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="font-display text-xl font-bold tracking-tight text-white">{{ $tournament->name }}</span>
                                <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-medium text-slate-200">
                                    {{ $tournament->category->label() }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-500/35 bg-orange-500/15 px-2.5 py-0.5 text-xs font-semibold text-orange-300">
                                    @if ($isLiveRegistration)
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    @endif
                                    {{ $tournament->status->label() }}
                                </span>
                                @if ($tournament->roster_locked)
                                    <span class="rounded-full border border-rose-500/35 bg-rose-500/15 px-2.5 py-0.5 text-xs font-semibold text-rose-300">
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
                            <a href="{{ route('schedule.index', ['tournament' => $tournament->id]) }}" wire:navigate
                               class="rounded-xl border border-amber-500/30 bg-amber-500/10 px-3.5 py-2 text-xs font-semibold text-amber-200 hover:bg-amber-500/20 transition">
                                Schedule
                            </a>
                            <a href="{{ route('teams.index', ['tournament' => $tournament->id]) }}" wire:navigate
                               class="rounded-xl border border-orange-500/30 bg-orange-500/10 px-3.5 py-2 text-xs font-semibold text-orange-200 hover:bg-orange-500/20 hover:border-orange-400/50 transition">
                                Teams ({{ $tournament->teams_count }})
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
