<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Players &amp; Rosters</h1>
            <p class="mt-1 text-sm text-slate-400">
                Manage player profiles, positions, import status, and unique jersey numbers per team.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
                class="rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
            + Add Player
        </button>
    </div>

    <div class="flex items-center gap-3">
        <label class="text-xs font-medium text-slate-400">Team Filter:</label>
        <select wire:model.live="selectedTeamId"
                class="rounded-xl border border-slate-700 bg-slate-900 px-3.5 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
            <option value="">All Teams</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}">{{ $team->name }} ({{ $team->tournament->name }})</option>
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
                {{ $editingId ? 'Edit Player' : 'Add Player to Roster' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-slate-300 mb-1">Team</label>
                    <select wire:model="team_id"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        <option value="">Select team...</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} ({{ $team->tournament->name }})</option>
                        @endforeach
                    </select>
                    @error('team_id') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">First Name</label>
                    <input wire:model="first_name" type="text" placeholder="e.g., Jericho"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('first_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Last Name</label>
                    <input wire:model="last_name" type="text" placeholder="e.g., Alvarez"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('last_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Jersey Number (00 or 0–99)</label>
                    <input wire:model="jersey_number" type="text" maxlength="3" placeholder="e.g., 23"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('jersey_number') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Position</label>
                    <select wire:model="position"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->value }}">{{ $pos->label() }}</option>
                        @endforeach
                    </select>
                    @error('position') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Birthdate</label>
                    <input wire:model="birthdate" type="date"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('birthdate') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col justify-center gap-2 pt-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input wire:model="is_import" type="checkbox" class="rounded border-slate-700 bg-slate-950 text-orange-600">
                        Import Player (non-resident)
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input wire:model="is_suspended" type="checkbox" class="rounded border-slate-700 bg-slate-950 text-rose-600">
                        Suspended
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button wire:click="cancel" type="button"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-slate-700 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-xl bg-orange-600 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
                    {{ $editingId ? 'Save Changes' : 'Add Player' }}
                </button>
            </div>
        </form>
    @endif

    <div class="rounded-2xl border border-slate-800 bg-slate-900/80 overflow-x-auto">
        @if ($players->isEmpty())
            <div class="p-8 text-center text-sm text-slate-400">
                No players found for this selection. Click <strong>+ Add Player</strong> to build the team roster.
            </div>
        @else
            <table class="min-w-full divide-y divide-slate-800 text-sm">
                <thead class="bg-slate-950/60 text-xs uppercase text-slate-400">
                    <tr>
                        <th class="px-5 py-3 text-left">#</th>
                        <th class="px-5 py-3 text-left">Player</th>
                        <th class="px-5 py-3 text-left">Team</th>
                        <th class="px-5 py-3 text-left">Pos</th>
                        <th class="px-5 py-3 text-left">Residency / Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($players as $player)
                        <tr wire:key="player-{{ $player->id }}" class="hover:bg-slate-800/40">
                            <td class="px-5 py-3 font-bold text-orange-400">#{{ $player->jersey_number }}</td>
                            <td class="px-5 py-3 font-medium text-white">
                                {{ $player->fullName }}
                                @if ($player->age() !== null)
                                    <span class="ml-1 text-xs text-slate-400">({{ $player->age() }} yrs)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-300">{{ $player->team->name }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded bg-slate-800 px-2 py-0.5 text-xs font-medium text-slate-200">
                                    {{ $player->position->value }}
                                </span>
                            </td>
                            <td class="px-5 py-3 space-x-1">
                                @if ($player->is_import)
                                    <span class="rounded-full bg-sky-500/15 px-2.5 py-0.5 text-xs text-sky-300">Import</span>
                                @else
                                    <span class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-xs text-emerald-300">Homegrown</span>
                                @endif
                                @if ($player->is_suspended)
                                    <span class="rounded-full bg-rose-500/15 px-2.5 py-0.5 text-xs text-rose-300">Suspended</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                                <button wire:click="edit({{ $player->id }})" type="button"
                                        class="text-xs font-medium text-slate-300 hover:text-white cursor-pointer">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $player->id }})" wire:confirm="Remove this player from the roster?" type="button"
                                        class="text-xs font-medium text-rose-400 hover:text-rose-300 cursor-pointer">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
