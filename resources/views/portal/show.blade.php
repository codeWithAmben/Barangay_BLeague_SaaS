<x-layouts::guest :title="$tenant->name . ' — League Portal'">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wider text-orange-400">Official Barangay League Portal</div>
                <h1 class="mt-1 text-3xl font-bold text-white">{{ $tenant->name }}</h1>
                <p class="mt-1 text-sm text-slate-400">
                    {{ collect([$tenant->municipality, $tenant->province])->filter()->implode(', ') ?: 'Philippines' }}
                </p>
            </div>

            <span class="inline-flex items-center self-start rounded-full bg-orange-500/15 px-3 py-1 text-xs font-semibold text-orange-300 ring-1 ring-inset ring-orange-500/30">
                {{ $tenant->plan_type->label() }}
            </span>
        </div>

        @forelse ($tournaments as $tournament)
            <div class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $tournament->name }}</h2>
                        <p class="text-xs text-slate-400">
                            {{ $tournament->category->label() }} Division &bull; {{ $tournament->format_type->label() }}
                        </p>
                    </div>
                    <span class="rounded-full bg-slate-800 px-3 py-1 text-xs font-medium text-slate-200">
                        {{ $tournament->status->label() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($tournament->teams as $team)
                        <div class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-xs font-bold text-white"
                                          style="background-color: {{ $team->primary_color }}">
                                        {{ strtoupper(substr($team->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <h3 class="font-semibold text-white">{{ $team->name }}</h3>
                                        <p class="text-xs text-slate-400">
                                            {{ $team->purok_zone ?? 'Barangay Squad' }}
                                            @if ($team->coach_name) &bull; Coach: {{ $team->coach_name }} @endif
                                        </p>
                                    </div>
                                </div>
                                <span class="text-xs text-slate-400">{{ $team->players->count() }} players</span>
                            </div>

                            @if ($team->players->isNotEmpty())
                                <div class="border-t border-slate-800 pt-3 grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs">
                                    @foreach ($team->players->sortBy(fn ($player) => (int) $player->jersey_number) as $player)
                                        <div class="flex items-center justify-between rounded-lg bg-slate-950/70 px-2.5 py-1.5">
                                            <span class="text-slate-200">
                                                <strong class="text-orange-400">#{{ $player->jersey_number }}</strong>
                                                {{ $player->fullName }}
                                            </span>
                                            <span class="text-slate-400">{{ $player->position->value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="md:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/60 p-6 text-sm text-slate-400">
                            Teams have not been published for this tournament yet.
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-8 text-center text-sm text-slate-400">
                No tournaments published yet by {{ $tenant->name }}.
            </div>
        @endforelse
    </section>
</x-layouts::guest>
