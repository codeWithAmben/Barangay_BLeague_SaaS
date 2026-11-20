<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold italic uppercase tracking-tight text-white">Tournaments</h1>
            </div>
            <p class="mt-1 text-sm text-slate-300">
                Create divisions (Midget, Junior, Senior, Open, 40-Up) and configure scheduling formats &amp; roster locks.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
                class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer self-start sm:self-auto">
            + New Tournament
        </button>
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
                    {{ $editingId ? 'Edit Tournament' : 'Create Tournament' }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Tournament Name</label>
                    <input wire:model="name" type="text" placeholder="e.g., SK Summer League 2026"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Category / Division</label>
                    <select wire:model="category"
                            class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($categories as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Format Type</label>
                    <select wire:model="format_type"
                            class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($formats as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('format_type') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Status</label>
                    <select wire:model="status"
                            class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($statuses as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Auto-Lock Roster After Game #</label>
                    <input wire:model="roster_lock_after_game" type="number" min="1" max="20"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('roster_lock_after_game') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Start Date</label>
                    <input wire:model="start_date" type="date"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('start_date') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">End Date</label>
                    <input wire:model="end_date" type="date"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('end_date') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button wire:click="cancel" type="button"
                        class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer">
                    {{ $editingId ? 'Save Changes' : 'Create Tournament' }}
                </button>
            </div>
        </form>
    @endif

    @if ($tournaments->isEmpty())
        <div class="arena-card rounded-2xl p-8 text-center text-sm text-slate-300">
            No tournaments created yet for {{ $tenant?->name }}. Click <strong class="text-orange-400">+ New Tournament</strong> to start.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($tournaments as $tournament)
                @php
                    $isLiveRegistration = in_array($tournament->status, [\App\Enums\TournamentStatus::Registration, \App\Enums\TournamentStatus::Ongoing], true);
                @endphp
                <div wire:key="tournament-{{ $tournament->id }}" class="arena-card rounded-2xl p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h3 class="font-display text-2xl font-bold tracking-tight text-white">{{ $tournament->name }}</h3>
                            <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-0.5 text-xs font-semibold text-slate-200">
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
                        <p class="text-xs text-slate-400">
                            {{ $tournament->format_type->label() }} &bull;
                            <span class="text-slate-200 font-medium">{{ $tournament->teams_count }} teams</span> &bull;
                            <span class="text-slate-200 font-medium">{{ $tournament->players_count }} players</span>
                            @if ($tournament->start_date)
                                &bull; Starts {{ $tournament->start_date->format('M j, Y') }}
                            @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('schedule.index', ['tournament' => $tournament->id]) }}" wire:navigate
                           class="rounded-xl border border-amber-500/35 bg-amber-500/15 px-3.5 py-2 text-xs font-semibold text-amber-200 hover:bg-amber-500/25 transition">
                            Schedule &amp; Standings
                        </a>
                        <a href="{{ route('teams.index', ['tournament' => $tournament->id]) }}" wire:navigate
                           class="rounded-xl border border-orange-500/35 bg-orange-500/15 px-3.5 py-2 text-xs font-semibold text-orange-200 hover:bg-orange-500/25 transition">
                            Manage Teams
                        </a>
                        <button wire:click="toggleRosterLock({{ $tournament->id }})" type="button"
                                class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:border-amber-400/40 hover:bg-white/10 transition cursor-pointer">
                            {{ $tournament->roster_locked ? 'Unlock Roster' : 'Lock Roster' }}
                        </button>
                        <button wire:click="edit({{ $tournament->id }})" type="button"
                                class="rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-semibold text-slate-200 hover:border-white/25 hover:bg-white/10 transition cursor-pointer">
                            Edit
                        </button>
                        <button wire:click="delete({{ $tournament->id }})" wire:confirm="Delete this tournament and all its teams?" type="button"
                                class="rounded-xl border border-rose-500/35 bg-rose-500/10 px-3.5 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
                            Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
