<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold italic uppercase tracking-tight text-white">Schedule &amp; Standings</h1>
            </div>
            <p class="mt-1 text-sm text-slate-300">
                Generate Berger round-robin matchups, record game scores, and track live FIBA standings &amp; tie-breakers.
            </p>
        </div>

        <button wire:click="toggleGenerator" type="button"
                class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer self-start sm:self-auto">
            {{ $showGenerator ? 'Close Generator' : 'Generate Round-Robin Schedule' }}
        </button>
    </div>

    <div class="arena-card rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <label class="text-xs font-semibold uppercase tracking-wider text-slate-300">Tournament:</label>
            <select wire:model.live="selectedTournamentId"
                    class="rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
                <option value="">Select tournament...</option>
                @foreach ($tournaments as $tournament)
                    <option value="{{ $tournament->id }}">{{ $tournament->name }} ({{ $tournament->teams_count }} teams)</option>
                @endforeach
            </select>
        </div>

        @if ($selectedTournament)
            <div class="flex items-center gap-2 text-xs text-slate-300">
                <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 font-semibold">{{ $selectedTournament->format_type->label() }}</span>
                <span class="rounded-full border border-orange-500/35 bg-orange-500/15 px-2.5 py-0.5 font-semibold text-orange-300">{{ $selectedTournament->category->label() }}</span>
            </div>
        @endif
    </div>

    @if ($errorBanner)
        <div class="rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            {{ $errorBanner }}
        </div>
    @endif

    @if ($showGenerator)
        <form wire:submit="generateSchedule" class="arena-card rounded-2xl p-6 space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
                <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-white">
                    Berger Table Round-Robin Generator
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Court / Venue</label>
                    <input wire:model="venue" type="text" placeholder="e.g., Brgy. San Roque Covered Court"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('venue') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Opening Date</label>
                    <input wire:model="start_date" type="date"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('start_date') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">First Tip-Off Time</label>
                    <input wire:model="first_tipoff" type="time"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('first_tipoff') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Games Per Game-Day</label>
                    <input wire:model="games_per_day" type="number" min="1" max="12"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('games_per_day') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Interval Between Games (mins)</label>
                    <input wire:model="minutes_between_games" type="number" min="30" max="240"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('minutes_between_games') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Days Between Game-Days</label>
                    <input wire:model="days_between_gamedays" type="number" min="1" max="14"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('days_between_gamedays') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2 flex items-center pt-5">
                    <label class="inline-flex items-center gap-2.5 text-sm text-slate-200 cursor-pointer">
                        <input wire:model="replace_scheduled" type="checkbox" class="rounded border-white/20 bg-[#06080d] text-orange-600">
                        Replace existing unplayed (scheduled) games while preserving live &amp; finished games
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button wire:click="toggleGenerator" type="button"
                        class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer">
                    Generate Matchups
                </button>
            </div>
        </form>
    @endif

    @if ($selectedTournament === null)
        <div class="arena-card rounded-2xl p-8 text-center text-sm text-slate-300">
            Create a tournament and register at least 2 teams before generating a schedule.
        </div>
    @else
        <!-- FIBA Standings Table -->
        <div class="arena-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                    <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-white">FIBA Standings</h2>
                </div>
                <span class="text-xs text-slate-400">
                    Tie-breakers: Head-to-Head &rarr; H2H Diff &rarr; Quotient (PF/PA) &rarr; Point Diff
                </span>
            </div>

            @if ($standings->isEmpty())
                <div class="p-8 text-center text-sm text-slate-300">
                    No teams registered in this tournament yet.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10 text-sm">
                        <thead class="bg-black/40 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Rank</th>
                                <th class="px-4 py-3 text-left">Team</th>
                                <th class="px-3 py-3 text-center">GP</th>
                                <th class="px-3 py-3 text-center">W</th>
                                <th class="px-3 py-3 text-center">L</th>
                                <th class="px-3 py-3 text-center">PTS</th>
                                <th class="px-3 py-3 text-center">PF</th>
                                <th class="px-3 py-3 text-center">PA</th>
                                <th class="px-3 py-3 text-center">DIFF</th>
                                <th class="px-4 py-3 text-right">Quotient</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach ($standings as $row)
                                <tr wire:key="standing-{{ $row['team']->id }}" class="hover:bg-white/[0.03] transition">
                                    <td class="px-4 py-3.5">
                                        <span class="font-display text-xl font-extrabold italic {{ $row['rank'] <= 4 ? 'text-orange-400' : 'text-slate-400' }}">
                                            #{{ $row['rank'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/15 font-display text-xs font-extrabold italic text-white"
                                                  style="background-color: {{ $row['team']->primary_color }}">
                                                {{ strtoupper(substr($row['team']->name, 0, 2)) }}
                                            </span>
                                            <div>
                                                <div class="font-semibold text-white">{{ $row['team']->name }}</div>
                                                @if ($row['tie_breaker_note'])
                                                    <div class="text-[11px] text-amber-300">Tie broken via {{ $row['tie_breaker_note'] }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5 text-center text-slate-300">{{ $row['games_played'] }}</td>
                                    <td class="px-3 py-3.5 text-center font-bold text-emerald-300">{{ $row['wins'] }}</td>
                                    <td class="px-3 py-3.5 text-center font-semibold text-rose-300">{{ $row['losses'] }}</td>
                                    <td class="px-3 py-3.5 text-center font-display text-lg font-extrabold text-white">{{ $row['points'] }}</td>
                                    <td class="px-3 py-3.5 text-center text-slate-300">{{ $row['points_for'] }}</td>
                                    <td class="px-3 py-3.5 text-center text-slate-300">{{ $row['points_against'] }}</td>
                                    <td class="px-3 py-3.5 text-center font-semibold {{ $row['point_differential'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                                        {{ $row['point_differential'] > 0 ? '+' : '' }}{{ $row['point_differential'] }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right font-mono text-xs font-semibold text-orange-300">
                                        {{ number_format($row['quotient'], 3) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Score Entry Modal / Inline Panel -->
        @if ($editingGameId !== null)
            <form wire:submit="saveScore" class="arena-card rounded-2xl border-orange-500/40 p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
                    <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-white">Record Game Score</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Home Score</label>
                        <input wire:model="homeScore" type="number" min="0" max="300"
                               class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @error('homeScore') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Away Score</label>
                        <input wire:model="awayScore" type="number" min="0" max="300"
                               class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @error('awayScore') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Status</label>
                        <select wire:model="gameStatus"
                                class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                            @foreach ($gameStatuses as $statusItem)
                                <option value="{{ $statusItem->value }}">{{ $statusItem->label() }}</option>
                            @endforeach
                        </select>
                        @error('gameStatus') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button wire:click="cancelScoreEditor" type="button"
                            class="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            class="btn-hoops-primary rounded-xl px-5 py-2 text-xs font-bold text-white cursor-pointer">
                        Save Final Score
                    </button>
                </div>
            </form>
        @endif

        <!-- Matchups by Round -->
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-white">Game Schedule</h2>
            </div>

            @if ($gamesByRound->isEmpty())
                <div class="arena-card rounded-2xl p-8 text-center text-sm text-slate-300">
                    No games scheduled yet for {{ $selectedTournament->name }}. Click <strong class="text-orange-400">Generate Round-Robin Schedule</strong> to create pairings automatically.
                </div>
            @else
                @foreach ($gamesByRound as $roundNumber => $roundGames)
                    <div wire:key="round-{{ $roundNumber }}" class="arena-card rounded-2xl overflow-hidden">
                        <div class="px-5 py-3 border-b border-white/10 bg-black/30 flex flex-wrap items-center justify-between gap-2">
                            <span class="font-display text-xl font-bold uppercase tracking-wide text-orange-400">
                                Round {{ $roundNumber }}
                            </span>
                            @if (isset($byeTeamsByRound[$roundNumber]))
                                <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs text-slate-300">
                                    BYE: {{ $byeTeamsByRound[$roundNumber]->name }}
                                </span>
                            @endif
                        </div>

                        <div class="divide-y divide-white/10">
                            @foreach ($roundGames as $game)
                                <div wire:key="game-{{ $game->id }}" class="px-5 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 hover:bg-white/[0.02] transition">
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5 flex-1">
                                        <div class="text-xs text-slate-400 min-w-[145px]">
                                            <div class="font-semibold text-slate-200">{{ $game->scheduled_at?->format('M j, Y · g:i A') ?? 'TBD' }}</div>
                                            <div>{{ $game->venue ?? 'Barangay Court' }}</div>
                                        </div>

                                        <div class="flex items-center gap-3 flex-wrap">
                                            <span class="font-display text-xl font-bold text-white">{{ $game->homeTeam->name }}</span>
                                            <span class="rounded-lg border border-white/10 bg-black/50 px-3 py-1 font-display text-xl font-extrabold italic text-orange-400">
                                                @if ($game->status === \App\Enums\GameStatus::Scheduled)
                                                    VS
                                                @else
                                                    {{ $game->home_score }} - {{ $game->away_score }}
                                                @endif
                                            </span>
                                            <span class="font-display text-xl font-bold text-white">{{ $game->awayTeam->name }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2.5">
                                        <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $game->status === \App\Enums\GameStatus::Finished ? 'border-emerald-500/35 bg-emerald-500/15 text-emerald-300' : ($game->status === \App\Enums\GameStatus::Live ? 'border-rose-500/35 bg-rose-500/15 text-rose-300' : 'border-white/10 bg-white/5 text-slate-300') }}">
                                            {{ $game->status->label() }}
                                        </span>
                                        <a href="{{ route('scorer.game', $game) }}" wire:navigate
                                           class="rounded-xl border border-amber-500/35 bg-amber-500/15 px-3 py-1.5 text-xs font-semibold text-amber-200 hover:bg-amber-500/25 transition">
                                            Live Scorer
                                        </a>
                                        <button wire:click="openScoreEditor({{ $game->id }})" type="button"
                                                class="rounded-xl border border-orange-500/35 bg-orange-500/15 px-3 py-1.5 text-xs font-semibold text-orange-200 hover:bg-orange-500/25 transition cursor-pointer">
                                            {{ $game->status === \App\Enums\GameStatus::Finished ? 'Edit Score' : 'Record Score' }}
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif
</div>
