<x-layouts::guest :title="$tenant->name . ' — League Portal'">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <div class="arena-glass rounded-2xl border border-white/10 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <x-league-badge :tenant="$tenant" size="lg" />
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-orange-400">Official Barangay League Portal</div>
                    <h1 class="mt-1 font-display text-3xl sm:text-4xl font-extrabold uppercase tracking-tight text-white">{{ $tenant->name }}</h1>
                    <p class="mt-1 text-sm text-[#C9D1E0]">
                        {{ collect([$tenant->municipality, $tenant->province])->filter()->implode(', ') ?: 'Philippines' }}
                    </p>
                </div>
            </div>

            <span class="inline-flex items-center self-start rounded-full bg-orange-500/15 px-3 py-1 text-xs font-semibold text-orange-300 ring-1 ring-inset ring-orange-500/30">
                {{ $tenant->plan_type->label() }}
            </span>
        </div>

        @forelse ($tournaments as $tournament)
            <div class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl font-bold text-white">{{ $tournament->name }}</h2>
                        <p class="text-sm text-[#C9D1E0]">
                            {{ $tournament->category->label() }} Division &bull; {{ $tournament->format_type->label() }}
                        </p>
                    </div>
                    <span class="rounded-full bg-[#132247] border border-white/10 px-3 py-1 text-xs font-semibold text-white">
                        {{ $tournament->status->label() }}
                    </span>
                </div>

                @php
                    $standings = $standingsByTournament[$tournament->id] ?? collect();
                @endphp

                @if ($standings->isNotEmpty())
                    <div class="arena-glass rounded-2xl border border-white/10 overflow-hidden">
                        <div class="px-5 py-3 border-b border-white/10 flex flex-wrap items-center justify-between gap-2">
                            <span class="font-display text-lg font-bold uppercase tracking-wide text-orange-400">Team Standings</span>
                            <span class="text-xs text-[#C9D1E0]">FIBA Ranking &bull; Quotient (PF/PA)</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10 text-sm">
                                <thead class="bg-black/35 text-xs uppercase text-[#C9D1E0]">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">#</th>
                                        <th class="px-4 py-2.5 text-left">Team</th>
                                        <th class="px-3 py-2.5 text-center">W</th>
                                        <th class="px-3 py-2.5 text-center">L</th>
                                        <th class="px-3 py-2.5 text-center">PTS</th>
                                        <th class="px-3 py-2.5 text-center">DIFF</th>
                                        <th class="px-4 py-2.5 text-right">Quotient</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10">
                                    @foreach ($standings as $row)
                                        <tr>
                                            <td class="px-4 py-2.5 font-display text-lg font-extrabold italic text-orange-400">{{ $row['rank'] }}</td>
                                            <td class="px-4 py-2.5 font-semibold text-white">{{ $row['team']->name }}</td>
                                            <td class="px-3 py-2.5 text-center font-bold text-emerald-300">{{ $row['wins'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-rose-300">{{ $row['losses'] }}</td>
                                            <td class="px-3 py-2.5 text-center font-bold text-white">{{ $row['points'] }}</td>
                                            <td class="px-3 py-2.5 text-center {{ $row['point_differential'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                                                {{ $row['point_differential'] > 0 ? '+' : '' }}{{ $row['point_differential'] }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right font-mono text-xs text-orange-300">{{ number_format($row['quotient'], 3) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($tournament->games->isNotEmpty())
                    <div class="arena-glass rounded-2xl border border-white/10 overflow-hidden">
                        <div class="px-5 py-3 border-b border-white/10 flex items-center justify-between">
                            <span class="font-display text-lg font-bold uppercase tracking-wide text-orange-400">Schedule &amp; Results</span>
                            <span class="text-xs text-[#C9D1E0]">{{ $tournament->games->count() }} games</span>
                        </div>
                        <div class="divide-y divide-white/10">
                            @foreach ($tournament->games as $game)
                                <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                                    <div class="text-xs text-[#C9D1E0]">
                                        R{{ $game->round_number ?? 1 }} &bull; {{ $game->scheduled_at?->format('M j, g:i A') ?? 'TBD' }}
                                        @if ($game->venue) &bull; {{ $game->venue }} @endif
                                    </div>
                                    <div class="flex items-center gap-2.5 font-semibold text-white">
                                        <span>{{ $game->homeTeam->name }}</span>
                                        <span class="rounded-md bg-black/50 border border-white/10 px-2.5 py-0.5 font-display text-base font-extrabold italic text-orange-400">
                                            @if ($game->status === \App\Enums\GameStatus::Scheduled)
                                                VS
                                            @else
                                                {{ $game->home_score }} - {{ $game->away_score }}
                                            @endif
                                        </span>
                                        <span>{{ $game->awayTeam->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-semibold {{ $game->status === \App\Enums\GameStatus::Finished ? 'text-emerald-300' : ($game->status === \App\Enums\GameStatus::Live ? 'text-rose-300' : 'text-[#C9D1E0]') }}">
                                            {{ $game->status->label() }}
                                        </span>
                                        <a href="{{ route('live.game', $game) }}"
                                           class="rounded-lg border border-orange-500/35 bg-orange-500/15 px-2.5 py-1 text-xs font-semibold text-orange-200 hover:bg-orange-500/25">
                                            Scoreboard &rarr;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @php
                    $awards = $awardsByTournament[$tournament->id] ?? null;
                @endphp

                @if ($awards && $awards['leaders']->isNotEmpty())
                    <div class="arena-glass rounded-2xl border border-white/10 overflow-hidden">
                        <div class="px-5 py-3 border-b border-white/10 flex flex-wrap items-center justify-between gap-2">
                            <span class="font-display text-lg font-bold uppercase tracking-wide text-orange-400">MVP Race &amp; League Leaders</span>
                            @if ($awards['mvp'])
                                <span class="text-xs font-semibold text-amber-300">
                                    MVP Leader: #{{ $awards['mvp']['player']->jersey_number }} {{ $awards['mvp']['player']->fullName }} ({{ number_format($awards['mvp']['sp_avg'], 1) }} SP/G)
                                </span>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/10 text-sm">
                                <thead class="bg-black/35 text-xs uppercase text-[#C9D1E0]">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Player</th>
                                        <th class="px-3 py-2.5 text-center">PPG</th>
                                        <th class="px-3 py-2.5 text-center">RPG</th>
                                        <th class="px-3 py-2.5 text-center">APG</th>
                                        <th class="px-3 py-2.5 text-center">SPG</th>
                                        <th class="px-3 py-2.5 text-center">BPG</th>
                                        <th class="px-4 py-2.5 text-right text-amber-300">SP/G</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10">
                                    @foreach ($awards['leaders']->take(5) as $row)
                                        <tr>
                                            <td class="px-4 py-2.5">
                                                <span class="font-semibold text-white">#{{ $row['player']->jersey_number }} {{ $row['player']->fullName }}</span>
                                                <span class="ml-1.5 text-xs text-[#C9D1E0]">({{ $row['team']->name }})</span>
                                            </td>
                                            <td class="px-3 py-2.5 text-center font-bold text-white">{{ number_format($row['ppg'], 1) }}</td>
                                            <td class="px-3 py-2.5 text-center text-[#C9D1E0]">{{ number_format($row['rpg'], 1) }}</td>
                                            <td class="px-3 py-2.5 text-center text-[#C9D1E0]">{{ number_format($row['apg'], 1) }}</td>
                                            <td class="px-3 py-2.5 text-center text-[#C9D1E0]">{{ number_format($row['spg'], 1) }}</td>
                                            <td class="px-3 py-2.5 text-center text-[#C9D1E0]">{{ number_format($row['bpg'], 1) }}</td>
                                            <td class="px-4 py-2.5 text-right font-bold text-amber-300">{{ number_format($row['sp_avg'], 1) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($tournament->teams as $team)
                        <div class="arena-glass rounded-2xl border border-white/10 p-5 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-xs font-bold text-white"
                                          style="background-color: {{ $team->primary_color }}">
                                        {{ strtoupper(substr($team->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <h3 class="font-semibold text-white">{{ $team->name }}</h3>
                                        <p class="text-sm text-[#C9D1E0]">
                                            {{ $team->purok_zone ?? 'Barangay Squad' }}
                                            @if ($team->coach_name) &bull; Coach: {{ $team->coach_name }} @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="text-sm text-[#C9D1E0]">{{ $team->players->count() }} players</span>
                            </div>

                            @if ($team->players->isNotEmpty())
                                <div class="border-t border-white/10 pt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                    @foreach ($team->players->sortBy(fn ($player) => (int) $player->jersey_number) as $player)
                                        <div class="flex items-center justify-between rounded-lg bg-[#050811]/70 px-3 py-2">
                                            <span class="text-white">
                                                <strong class="text-orange-400">#{{ $player->jersey_number }}</strong>
                                                {{ $player->fullName }}
                                            </span>
                                            <span class="text-[#C9D1E0]">{{ $player->position->value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="md:col-span-2 arena-glass rounded-2xl border border-white/10 p-6 text-sm text-[#C9D1E0]">
                            Teams have not been published for this tournament yet.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="arena-glass rounded-2xl border border-white/10 p-8 text-center text-base text-[#C9D1E0]">
                No published tournaments yet by {{ $tenant->name }}.
            </div>
        @endforelse
    </section>
</x-layouts::guest>
