<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Tournaments</h1>
            <p class="mt-1 text-sm text-slate-400">
                Create divisions (Midget, Junior, Senior, Open, 40-Up) and configure scheduling formats &amp; roster locks.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
                class="rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
            + New Tournament
        </button>
    </div>

    @if ($errorBanner)
        <div class="rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            {{ $errorBanner }}
        </div>
    @endif

    @if ($showForm)
        <form wire:submit="save" class="rounded-2xl border border-slate-800 bg-slate-900 p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">
                {{ $editingId ? 'Edit Tournament' : 'Create Tournament' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Tournament Name</label>
                    <input wire:model="name" type="text" placeholder="e.g., SK Summer League 2026"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Category / Division</label>
                    <select wire:model="category"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($categories as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Format Type</label>
                    <select wire:model="format_type"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($formats as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('format_type') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Status</label>
                    <select wire:model="status"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($statuses as $item)
                            <option value="{{ $item->value }}">{{ $item->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Auto-Lock Roster After Game #</label>
                    <input wire:model="roster_lock_after_game" type="number" min="1" max="20"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('roster_lock_after_game') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Start Date</label>
                    <input wire:model="start_date" type="date"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('start_date') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">End Date</label>
                    <input wire:model="end_date" type="date"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('end_date') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button wire:click="cancel" type="button"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-xl bg-orange-600 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
                    {{ $editingId ? 'Save Changes' : 'Create Tournament' }}
                </button>
            </div>
        </form>
    @endif

    <div class="rounded-2xl border border-slate-800 bg-slate-900/80 overflow-hidden">
        @if ($tournaments->isEmpty())
            <div class="p-8 text-center text-sm text-slate-400">
                No tournaments created yet for {{ $tenant?->name }}. Click <strong>+ New Tournament</strong> to start.
            </div>
        @else
            <div class="divide-y divide-slate-800">
                @foreach ($tournaments as $tournament)
                    <div wire:key="tournament-{{ $tournament->id }}" class="p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-semibold text-white">{{ $tournament->name }}</h3>
                                <span class="rounded-full bg-slate-800 px-2.5 py-0.5 text-xs text-slate-300">
                                    {{ $tournament->category->label() }}
                                </span>
                                <span class="rounded-full bg-orange-500/15 px-2.5 py-0.5 text-xs text-orange-300">
                                    {{ $tournament->status->label() }}
                                </span>
                                @if ($tournament->roster_locked)
                                    <span class="rounded-full bg-rose-500/15 px-2.5 py-0.5 text-xs text-rose-300">
                                        Roster Locked
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $tournament->format_type->label() }} &bull;
                                {{ $tournament->teams_count }} teams &bull;
                                {{ $tournament->players_count }} players
                                @if ($tournament->start_date)
                                    &bull; Starts {{ $tournament->start_date->format('M j, Y') }}
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('teams.index', ['tournament' => $tournament->id]) }}" wire:navigate
                               class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700">
                                Manage Teams
                            </a>
                            <button wire:click="toggleRosterLock({{ $tournament->id }})" type="button"
                                    class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700 cursor-pointer">
                                {{ $tournament->roster_locked ? 'Unlock Roster' : 'Lock Roster' }}
                            </button>
                            <button wire:click="edit({{ $tournament->id }})" type="button"
                                    class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700 cursor-pointer">
                                Edit
                            </button>
                            <button wire:click="delete({{ $tournament->id }})" wire:confirm="Delete this tournament and all its teams?" type="button"
                                    class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-300 hover:bg-rose-500/20 cursor-pointer">
                                Delete
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
