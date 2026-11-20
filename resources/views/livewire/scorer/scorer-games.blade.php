<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold italic uppercase tracking-tight text-white">Courtside Live Scorer</h1>
            </div>
            <p class="mt-1 text-sm text-slate-300">
                Select an assigned match to launch the tablet scoring console or share the public live scoreboard link.
            </p>
        </div>
    </div>

    @foreach (['Live Now' => $liveGames, 'Scheduled Matches' => $scheduledGames, 'Completed Games' => $finishedGames] as $sectionTitle => $sectionGames)
        @if ($sectionGames->isNotEmpty())
            <div class="arena-card rounded-2xl overflow-hidden">
                <div class="px-6 py-3.5 border-b border-white/10 flex items-center justify-between">
                    <span class="font-display text-xl font-bold uppercase tracking-wide text-orange-400">{{ $sectionTitle }}</span>
                    <span class="text-xs text-slate-400">{{ $sectionGames->count() }} {{ Str::plural('game', $sectionGames->count()) }}</span>
                </div>

                <div class="divide-y divide-white/10">
                    @foreach ($sectionGames as $game)
                        <div class="px-6 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 hover:bg-white/[0.02] transition">
                            <div class="space-y-1">
                                <div class="text-xs text-slate-400">
                                    {{ $game->tournament->name }} &bull;
                                    Round {{ $game->round_number ?? 1 }} &bull;
                                    {{ $game->scheduled_at?->format('M j, Y · g:i A') ?? 'TBD' }}
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="font-display text-2xl font-bold text-white">{{ $game->homeTeam->name }}</span>
                                    <span class="rounded-lg border border-white/10 bg-black/50 px-3 py-1 font-display text-xl font-extrabold italic text-orange-400">
                                        {{ $game->home_score }} - {{ $game->away_score }}
                                    </span>
                                    <span class="font-display text-2xl font-bold text-white">{{ $game->awayTeam->name }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2.5">
                                <a href="{{ route('live.game', $game) }}" target="_blank"
                                   class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 transition">
                                    Public Live Link ↗
                                </a>
                                <a href="{{ route('scorer.game', $game) }}" wire:navigate
                                   class="btn-hoops-primary rounded-xl px-4 py-2 text-xs font-bold text-white">
                                    Open Scorer Console &rsaquo;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    @if ($liveGames->isEmpty() && $scheduledGames->isEmpty() && $finishedGames->isEmpty())
        <div class="arena-card rounded-2xl p-8 text-center text-sm text-slate-300">
            No games scheduled yet. Ask your League Commissioner to generate the tournament schedule first.
        </div>
    @endif
</div>
