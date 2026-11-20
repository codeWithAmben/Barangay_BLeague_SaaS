<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8" wire:poll.10s>
    <!-- Live Header Banner -->
    <div class="arena-glass rounded-3xl border border-white/15 p-6 sm:p-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('portal.show', $game->tenant) }}" class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400 hover:text-orange-300">
                    &larr; {{ $game->tenant->name }} &bull; {{ $game->tournament->name }}
                </a>
                <div class="mt-1 text-sm text-[#C9D1E0]">
                    Round {{ $game->round_number ?? 1 }} &bull; {{ $game->venue ?? 'Barangay Covered Court' }}
                </div>
            </div>

            <span class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1 text-xs font-bold uppercase tracking-wider {{ $game->status === \App\Enums\GameStatus::Live ? 'border-rose-500/45 bg-rose-500/20 text-rose-200' : ($game->status === \App\Enums\GameStatus::Finished ? 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200' : 'border-white/15 bg-white/5 text-[#C9D1E0]') }}">
                @if ($game->status === \App\Enums\GameStatus::Live)
                    <span class="h-2 w-2 rounded-full bg-rose-400 animate-pulse"></span>
                @endif
                {{ $game->status->label() }}
            </span>
        </div>

        <!-- Broadcast Scoreboard -->
        <div class="grid grid-cols-1 md:grid-cols-11 gap-6 items-center">
            <div class="md:col-span-4 flex items-center justify-between md:justify-start gap-4">
                <div class="flex items-center gap-3.5">
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-white/20 font-display text-xl font-extrabold italic text-white shadow-lg"
                          style="background-color: {{ $game->homeTeam->primary_color }}">
                        {{ strtoupper(substr($game->homeTeam->name, 0, 2)) }}
                    </span>
                    <div>
                        <h1 class="font-display text-3xl font-extrabold text-white leading-tight">{{ $game->homeTeam->name }}</h1>
                        <div class="flex items-center gap-2 text-xs text-[#C9D1E0] mt-1">
                            <span>Fouls: {{ $periodSummary['current_quarter_fouls']['home'] }}</span>
                            @if ($periodSummary['home_penalty'])
                                <span class="rounded bg-rose-500/20 border border-rose-500/40 px-1.5 py-0.5 text-[10px] font-bold text-rose-200">PENALTY</span>
                            @endif
                            <span>TO: {{ $periodSummary['timeouts_remaining']['home'] }}</span>
                        </div>
                    </div>
                </div>
                <x-flip-score :score="$game->home_score" accent="orange" size="lg" />
            </div>

            <div class="md:col-span-3 flex flex-col items-center justify-center text-center border-y md:border-y-0 md:border-x border-white/10 py-3 md:py-0">
                <div class="font-display text-3xl font-extrabold uppercase text-white">
                    {{ \App\Services\GameScoringService::quarterLabel($game->current_quarter) }}
                </div>
                <div class="text-xs text-[#C9D1E0] mt-1">Real-Time Courtside Feed</div>
            </div>

            <div class="md:col-span-4 flex items-center justify-between md:justify-end gap-4">
                <x-flip-score :score="$game->away_score" accent="white" size="lg" class="order-2 md:order-1" />
                <div class="flex items-center gap-3.5 order-1 md:order-2">
                    <div class="md:text-right">
                        <h2 class="font-display text-3xl font-extrabold text-white leading-tight">{{ $game->awayTeam->name }}</h2>
                        <div class="flex flex-wrap items-center md:justify-end gap-2 text-xs text-[#C9D1E0] mt-1">
                            <span>Fouls: {{ $periodSummary['current_quarter_fouls']['away'] }}</span>
                            @if ($periodSummary['away_penalty'])
                                <span class="rounded bg-rose-500/20 border border-rose-500/40 px-1.5 py-0.5 text-[10px] font-bold text-rose-200">PENALTY</span>
                            @endif
                            <span>TO: {{ $periodSummary['timeouts_remaining']['away'] }}</span>
                        </div>
                    </div>
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-white/20 font-display text-xl font-extrabold italic text-white shadow-lg"
                          style="background-color: {{ $game->awayTeam->primary_color }}">
                        {{ strtoupper(substr($game->awayTeam->name, 0, 2)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Quarter-by-Quarter Breakdown -->
        <div class="overflow-x-auto border-t border-white/10 pt-4">
            <table class="min-w-full text-sm">
                <thead class="text-xs uppercase text-[#C9D1E0]">
                    <tr>
                        <th class="py-1.5 text-left">Team</th>
                        @foreach ($periodSummary['quarters'] as $qNum => $scores)
                            <th class="py-1.5 text-center">{{ \App\Services\GameScoringService::quarterLabel($qNum) }}</th>
                        @endforeach
                        <th class="py-1.5 text-right text-orange-400">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    <tr>
                        <td class="py-2 font-semibold text-white">{{ $game->homeTeam->name }}</td>
                        @foreach ($periodSummary['quarters'] as $scores)
                            <td class="py-2 text-center text-[#C9D1E0]">{{ $scores['home'] }}</td>
                        @endforeach
                        <td class="py-2 text-right font-display text-xl font-extrabold text-orange-400">{{ $game->home_score }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-semibold text-white">{{ $game->awayTeam->name }}</td>
                        @foreach ($periodSummary['quarters'] as $scores)
                            <td class="py-2 text-center text-[#C9D1E0]">{{ $scores['away'] }}</td>
                        @endforeach
                        <td class="py-2 text-right font-display text-xl font-extrabold text-white">{{ $game->away_score }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if ($game->bestPlayer)
        <div class="arena-glass rounded-2xl border border-amber-400/40 p-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="text-xs font-bold uppercase tracking-[0.2em] text-amber-300">Best Player of the Game</div>
                <div class="font-display text-3xl font-extrabold text-white mt-0.5">
                    #{{ $game->bestPlayer->jersey_number }} {{ $game->bestPlayer->fullName }}
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full bg-amber-500/15 border border-amber-400/40 px-4 py-1 text-xs font-bold text-amber-200">
                    Top Efficiency (EFF)
                </span>
                @if ($bpgPoster)
                    <button wire:click="togglePoster" type="button"
                            class="btn-hoops-primary rounded-xl px-4 py-2 text-xs font-bold text-white cursor-pointer">
                        {{ $showPoster ? 'Hide BPG Poster' : 'Download BPG Poster' }}
                    </button>
                @endif
            </div>
        </div>

        @if ($showPoster && $bpgPoster)
            <x-bpg-poster-generator :poster="$bpgPoster" on-close="togglePoster" />
        @endif
    @endif

    <!-- Box Scores -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach ([['team' => $game->homeTeam, 'rows' => $homeBox], ['team' => $game->awayTeam, 'rows' => $awayBox]] as $teamBox)
            <div class="arena-glass rounded-2xl border border-white/10 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-white/10 font-display text-xl font-bold text-white">
                    {{ $teamBox['team']->name }} Box Score
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10 text-xs sm:text-sm">
                        <thead class="bg-black/35 text-[11px] uppercase text-[#C9D1E0]">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Player</th>
                                <th class="px-2 py-2.5 text-center">PTS</th>
                                <th class="px-2 py-2.5 text-center">REB</th>
                                <th class="px-2 py-2.5 text-center">AST</th>
                                <th class="px-2 py-2.5 text-center">STL</th>
                                <th class="px-2 py-2.5 text-center">BLK</th>
                                <th class="px-2 py-2.5 text-center">PF</th>
                                <th class="px-3 py-2.5 text-right text-orange-400">EFF</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @forelse ($teamBox['rows'] as $row)
                                <tr>
                                    <td class="px-4 py-2.5 font-medium text-white">
                                        <span class="text-orange-400 font-bold">#{{ $row['player']->jersey_number }}</span>
                                        {{ $row['player']->fullName }}
                                    </td>
                                    <td class="px-2 py-2.5 text-center font-bold text-white">{{ $row['pts'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-[#C9D1E0]">{{ $row['reb'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-[#C9D1E0]">{{ $row['ast'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-[#C9D1E0]">{{ $row['stl'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-[#C9D1E0]">{{ $row['blk'] }}</td>
                                    <td class="px-2 py-2.5 text-center text-[#C9D1E0]">{{ $row['pf'] }}</td>
                                    <td class="px-3 py-2.5 text-right font-bold text-orange-300">{{ $row['eff'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-xs text-[#C9D1E0]">Roster stats will appear once tip-off starts.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Play-by-Play -->
    <div class="arena-glass rounded-2xl border border-white/10 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-white/10 font-display text-xl font-bold uppercase text-orange-400">
            Live Play-by-Play
        </div>
        <div class="divide-y divide-white/10">
            @forelse ($recentEvents as $event)
                <div class="px-5 py-2.5 flex items-center justify-between gap-3 text-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="rounded bg-white/10 px-2 py-0.5 text-xs font-bold text-orange-300">
                            {{ \App\Services\GameScoringService::quarterLabel($event->quarter) }} {{ $event->game_clock ?? '' }}
                        </span>
                        <span class="font-semibold text-white">{{ $event->event_type->label() }}</span>
                        @if ($event->player)
                            <span class="text-[#C9D1E0]">#{{ $event->player->jersey_number }} {{ $event->player->fullName }}</span>
                        @endif
                    </div>
                    <span class="text-xs text-[#C9D1E0] shrink-0">{{ $event->team->name }}</span>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-[#C9D1E0]">Waiting for tip-off plays...</div>
            @endforelse
        </div>
    </div>
</div>

@script
<script>
    if (window.Echo) {
        window.Echo.channel('game.{{ $game->id }}')
            .listen('.game.updated', () => {
                $wire.$refresh();
            });
    }
</script>
@endscript
