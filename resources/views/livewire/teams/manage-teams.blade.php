<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Teams &amp; Puroks</h1>
            <p class="mt-1 text-sm text-slate-400">
                Register barangay/purok teams, coaches, and jersey colors per tournament.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
                class="rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
            + Register Team
        </button>
    </div>

    <div class="flex items-center gap-3">
        <label class="text-xs font-medium text-slate-400">Tournament Filter:</label>
        <select wire:model.live="selectedTournamentId"
                class="rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
            <option value="">All Tournaments</option>
            @foreach ($tournaments as $tournament)
                <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
            @endforeach
        </select>
    </div>

    @if ($errorBanner)
        <div class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            {{ $errorBanner }}
        </div>
    @endif

    @if ($showForm)
        <form wire:submit="save" class="rounded-2xl border border-slate-800 bg-slate-900 p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">
                {{ $editingId ? 'Edit Team' : 'Register New Team' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tournament</label>
                    <select wire:model="tournament_id"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        <option value="">Select tournament...</option>
                        @foreach ($tournaments as $tournament)
                            <option value="{{ $tournament->id }}">{{ $tournament->name }}</option>
                        @endforeach
                    </select>
                    @error('tournament_id') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Team Name</label>
                    <input wire:model="name" type="text" placeholder="e.g., Purok 3 Warriors"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Purok / Zone</label>
                    <input wire:model="purok_zone" type="text" placeholder="e.g., Purok 3"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('purok_zone') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Head Coach</label>
                    <input wire:model="coach_name" type="text" placeholder="Coach full name"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('coach_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Team Manager</label>
                    <input wire:model="manager_name" type="text" placeholder="Optional"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('manager_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Primary Color</label>
                        <input wire:model="primary_color" type="color"
                               class="h-10 w-full rounded-xl border border-slate-700 bg-slate-950 p-1 cursor-pointer">
                        @error('primary_color') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Secondary Color</label>
                        <input wire:model="secondary_color" type="color"
                               class="h-10 w-full rounded-xl border border-slate-700 bg-slate-950 p-1 cursor-pointer">
                        @error('secondary_color') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button wire:click="cancel" type="button"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-xl bg-orange-600 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
                    {{ $editingId ? 'Save Changes' : 'Register Team' }}
                </button>
            </div>
        </form>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse ($teams as $team)
            <div wire:key="team-{{ $team->id }}" class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 flex flex-col justify-between gap-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl text-sm font-bold text-white shadow"
                              style="background-color: {{ $team->primary_color }}">
                            {{ strtoupper(substr($team->name, 0, 2)) }}
                        </span>
                        <div>
                            <h3 class="font-semibold text-white">{{ $team->name }}</h3>
                            <p class="text-xs text-slate-400">
                                {{ $team->tournament->name }}
                                @if ($team->purok_zone) &bull; {{ $team->purok_zone }} @endif
                            </p>
                        </div>
                    </div>
                    <span class="rounded-full bg-slate-800 px-2.5 py-0.5 text-xs text-slate-300">
                        {{ $team->players_count }} players
                    </span>
                </div>

                @if ($team->coach_name || $team->manager_name)
                    <div class="text-xs text-slate-400">
                        @if ($team->coach_name) Coach: <span class="text-slate-200">{{ $team->coach_name }}</span> @endif
                        @if ($team->manager_name) &bull; Manager: <span class="text-slate-200">{{ $team->manager_name }}</span> @endif
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800">
                    <a href="{{ route('players.index', ['team' => $team->id]) }}" wire:navigate
                       class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700">
                        Manage Roster
                    </a>
                    <button wire:click="edit({{ $team->id }})" type="button"
                            class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700 cursor-pointer">
                        Edit
                    </button>
                    <button wire:click="delete({{ $team->id }})" wire:confirm="Delete this team and its roster?" type="button"
                            class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-300 hover:bg-rose-500/20 cursor-pointer">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 rounded-2xl border border-slate-800 bg-slate-900/80 p-8 text-center text-sm text-slate-400">
                No teams registered yet. Click <strong>+ Register Team</strong> to add your Purok squads.
            </div>
        @endforelse
    </div>
</div>
