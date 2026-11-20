<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold italic uppercase tracking-tight text-white">Teams &amp; Puroks</h1>
            </div>
            <p class="mt-1 text-sm text-slate-300">
                Register barangay/purok teams, coaches, and jersey colors per tournament.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
                class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer self-start sm:self-auto">
            + Register Team
        </button>
    </div>

    <div class="arena-card rounded-2xl px-4 py-3 flex flex-wrap items-center gap-3">
        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300">Tournament Filter:</label>
        <select wire:model.live="selectedTournamentId"
                class="rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
            <option value="">All Tournaments</option>
            @foreach ($tournaments as $tournament)
                <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($errorBanner)
        <div class="rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            {{ $errorBanner }}
        </div>
    @endif

    @if ($showForm)
        <form wire:submit="save" class="arena-card rounded-2xl p-6 space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
                <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-white">
                    {{ $editingId ? 'Edit Team' : 'Register New Team' }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tournament</label>
                    <select wire:model="tournament_id"
                            class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        <option value="">Select tournament...</option>
                        @foreach ($tournaments as $tournament)
                            <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
                        @endforeach
                    </select>
                    @error('tournament_id') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Team Name</label>
                    <input wire:model="name" type="text" placeholder="e.g., Purok 3 Warriors"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Purok / Zone</label>
                    <input wire:model="purok_zone" type="text" placeholder="e.g., Purok 3"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('purok_zone') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Head Coach</label>
                    <input wire:model="coach_name" type="text" placeholder="Coach full name"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('coach_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Team Manager</label>
                    <input wire:model="manager_name" type="text" placeholder="Optional"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('manager_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Primary Color</label>
                        <input wire:model="primary_color" type="color"
                               class="h-10 w-full rounded-xl border border-white/10 bg-[#06080d] p-1 cursor-pointer">
                        @error('primary_color') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Secondary Color</label>
                        <input wire:model="secondary_color" type="color"
                               class="h-10 w-full rounded-xl border border-white/10 bg-[#06080d] p-1 cursor-pointer">
                        @error('secondary_color') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button wire:click="cancel" type="button"
                        class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer">
                    {{ $editingId ? 'Save Changes' : 'Register Team' }}
                </button>
            </div>
        </form>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse ($teams as $team)
            <div wire:key="team-{{ $team->id }}" class="arena-card rounded-2xl overflow-hidden flex flex-col justify-between">
                <!-- Colored Top-Edge Jersey Stripe -->
                <div class="h-1.5 w-full" style="background: linear-gradient(90deg, {{ $team->primary_color }}, {{ $team->secondary_color }})"></div>

                <div class="p-5 flex-1 flex flex-col justify-between gap-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <!-- Athletic Jersey Crest Badge with Diagonal Sheen -->
                            <span class="relative inline-flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/20 font-display text-lg font-extrabold italic text-white shadow-lg"
                                  style="background-color: {{ $team->primary_color }}">
                                <span aria-hidden="true" class="pointer-events-none absolute inset-0 bg-gradient-to-br from-white/30 via-transparent to-black/35"></span>
                                <span class="relative drop-shadow">{{ strtoupper(substr($team->name, 0, 2)) }}</span>
                            </span>
                            <div>
                                <h3 class="font-display text-2xl font-bold tracking-tight text-white leading-tight">{{ $team->name }}</h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $team->tournament->name }}
                                    @if ($team->purok_zone) &bull; {{ $team->purok_zone }} @endif
                                </p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-semibold text-slate-200">
                            {{ $team->players_count }} players
                        </span>
                    </div>

                    @if ($team->coach_name || $team->manager_name)
                        <div class="text-xs text-slate-400">
                            @if ($team->coach_name) Coach: <span class="text-slate-200 font-medium">{{ $team->coach_name }}</span> @endif
                            @if ($team->manager_name) &bull; Manager: <span class="text-slate-200 font-medium">{{ $team->manager_name }}</span> @endif
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-white/10">
                        <a href="{{ route('players.index', ['team' => $team->id]) }}" wire:navigate
                           class="rounded-xl border border-orange-500/40 bg-orange-500/15 px-3.5 py-2 text-xs font-semibold text-orange-200 hover:bg-orange-500/25 transition">
                            Manage Roster
                        </a>
                        <button wire:click="edit({{ $team->id }})" type="button"
                                class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 transition cursor-pointer">
                            Edit
                        </button>
                        <button wire:click="delete({{ $team->id }})" wire:confirm="Delete this team and its roster?" type="button"
                                class="rounded-xl border border-rose-500/35 bg-rose-500/10 px-3.5 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 arena-card rounded-2xl p-8 text-center text-sm text-slate-300">
                No teams registered yet. Click <strong class="text-orange-400">+ Register Team</strong> to add your Purok squads.
            </div>
        @endforelse
    </div>
</div>
