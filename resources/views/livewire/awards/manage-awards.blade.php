<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold italic uppercase tracking-tight text-white">Awards &amp; Leaderboards</h1>
            </div>
            <p class="mt-1 text-sm text-slate-300">
                Season MVP race (Statistical Points), Mythical Five, stat leaders, and Facebook-ready BPG posters.
            </p>
        </div>
    </div>

    <div class="arena-card rounded-2xl px-4 py-3 flex flex-wrap items-center gap-3">
        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300">Tournament:</label>
        <select wire:model.live="selectedTournamentId"
                class="rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
            <option value="">Select tournament...</option>
            @foreach ($tournaments as $tournament)
                <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($posterData)
        <x-bpg-poster-generator :poster="$posterData" on-close="closePoster" />
    @endif

    @if ($selectedTournament === null)
        <div class="arena-card rounded-2xl p-8 text-center text-sm text-slate-300">
            Select a tournament to view season leaders and BPG posters.
        </div>
    @else
        <!-- Season MVP + Mythical Five -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <div class="lg:col-span-5 arena-card arena-chamfer p-6 space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/40 bg-amber-500/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-300">
                    Season MVP Frontrunner
                </div>
                @if ($awards['mvp'])
                    @php $mvp = $awards['mvp']; @endphp
                    <div class="font-display text-4xl font-extrabold italic text-white leading-none pt-1">
                        #{{ $mvp['player']->jersey_number }} {{ $mvp['player']->fullName }}
                    </div>
                    <div class="text-sm text-slate-300">
                        {{ $mvp['team']->name }} &bull; {{ $mvp['player']->position->value }}
                    </div>
                    <div class="pt-2 grid grid-cols-4 gap-2 text-center">
                        <div class="rounded-xl bg-white/5 p-2">
                            <div class="font-display text-2xl font-extrabold text-amber-300">{{ number_format($mvp['sp_avg'], 1) }}</div>
                            <div class="text-[11px] text-slate-400">SP/G</div>
                        </div>
                        <div class="rounded-xl bg-white/5 p-2">
                            <div class="font-display text-2xl font-extrabold text-white">{{ number_format($mvp['ppg'], 1) }}</div>
                            <div class="text-[11px] text-slate-400">PPG</div>
                        </div>
                        <div class="rounded-xl bg-white/5 p-2">
                            <div class="font-display text-2xl font-extrabold text-white">{{ number_format($mvp['rpg'], 1) }}</div>
                            <div class="text-[11px] text-slate-400">RPG</div>
                        </div>
                        <div class="rounded-xl bg-white/5 p-2">
                            <div class="font-display text-2xl font-extrabold text-white">{{ number_format($mvp['apg'], 1) }}</div>
                            <div class="text-[11px] text-slate-400">APG</div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-slate-400 pt-2">MVP race updates once games are scored and finalized.</p>
                @endif
            </div>

            <div class="lg:col-span-7 arena-card rounded-2xl p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
                    <h2 class="font-display text-2xl font-bold uppercase text-white">Mythical Five Race</h2>
                </div>

                @if ($awards['mythical_five']->isEmpty())
                    <p class="text-sm text-slate-400">Top 5 positional leaders will populate automatically after finalized games.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        @foreach ($awards['mythical_five'] as $star)
                            <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-orange-400">{{ $star['player']->position->value }}</span>
                                    <span class="font-bold text-amber-300">{{ number_format($star['sp_avg'], 1) }} SP</span>
                                </div>
                                <div class="font-display text-lg font-bold text-white leading-tight">
                                    #{{ $star['player']->jersey_number }} {{ $star['player']->last_name }}
                                </div>
                                <div class="text-[11px] text-slate-400 truncate">{{ $star['team']->name }}</div>
                                <div class="text-[11px] text-slate-300">{{ number_format($star['ppg'], 1) }}p / {{ number_format($star['rpg'], 1) }}r / {{ number_format($star['apg'], 1) }}a</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Season Statistical Leaderboard -->
        <div class="arena-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-3">
                    <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
                    <h2 class="font-display text-2xl font-bold uppercase text-white">Season Statistical Leaders</h2>
                </div>
                <span class="text-xs text-slate-400">Ranked by Statistical Points per Game (SP/G)</span>
            </div>

            @if ($awards['leaders']->isEmpty())
                <div class="p-8 text-center text-sm text-slate-300">
                    No player game stats logged for finished games yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10 text-sm">
                        <thead class="bg-black/40 text-xs uppercase text-slate-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Rank</th>
                                <th class="px-4 py-3 text-left">Player</th>
                                <th class="px-3 py-3 text-center">GP</th>
                                <th class="px-3 py-3 text-center">PPG</th>
                                <th class="px-3 py-3 text-center">RPG</th>
                                <th class="px-3 py-3 text-center">APG</th>
                                <th class="px-3 py-3 text-center">SPG</th>
                                <th class="px-3 py-3 text-center">BPG</th>
                                <th class="px-3 py-3 text-center">3PT%</th>
                                <th class="px-4 py-3 text-right text-amber-300">SP/G</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach ($awards['leaders'] as $index => $row)
                                <tr class="hover:bg-white/[0.03] transition">
                                    <td class="px-4 py-3 font-display text-lg font-extrabold italic text-orange-400">#{{ $index + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-white">#{{ $row['player']->jersey_number }} {{ $row['player']->fullName }}</div>
                                        <div class="text-xs text-slate-400">{{ $row['team']->name }} &bull; {{ $row['player']->position->value }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-center text-slate-300">{{ $row['games_played'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-white">{{ number_format($row['ppg'], 1) }}</td>
                                    <td class="px-3 py-3 text-center text-slate-300">{{ number_format($row['rpg'], 1) }}</td>
                                    <td class="px-3 py-3 text-center text-slate-300">{{ number_format($row['apg'], 1) }}</td>
                                    <td class="px-3 py-3 text-center text-slate-300">{{ number_format($row['spg'], 1) }}</td>
                                    <td class="px-3 py-3 text-center text-slate-300">{{ number_format($row['bpg'], 1) }}</td>
                                    <td class="px-3 py-3 text-center text-slate-300">{{ number_format($row['three_pct'], 1) }}%</td>
                                    <td class="px-4 py-3 text-right font-display text-xl font-extrabold text-amber-300">{{ number_format($row['sp_avg'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Finished Games & 1-Click BPG Poster Export -->
        <div class="arena-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
                <h2 class="font-display text-2xl font-bold uppercase text-white">Best Player of the Game (BPG) Posters</h2>
            </div>

            @if ($finishedGames->isEmpty())
                <div class="p-8 text-center text-sm text-slate-300">
                    Finalize a game in the Live Scorer to generate its Facebook-ready BPG poster.
                </div>
            @else
                <div class="divide-y divide-white/10">
                    @foreach ($finishedGames as $game)
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <div class="font-display text-xl font-bold text-white">
                                    {{ $game->homeTeam->name }} {{ $game->home_score }} - {{ $game->away_score }} {{ $game->awayTeam->name }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    BPG: <span class="text-amber-300 font-semibold">{{ $game->bestPlayer?->fullName ?? 'Top Scorer / EFF' }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button wire:click="openPoster({{ $game->id }})" type="button"
                                        class="btn-hoops-primary rounded-xl px-4 py-2 text-xs font-bold text-white cursor-pointer">
                                    Generate BPG Poster
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
