<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold italic uppercase tracking-tight text-white">Players &amp; Rosters</h1>
            </div>
            <p class="mt-1 text-sm text-slate-300">
                Manage player profiles, positions, import status, and unique jersey numbers per team.
            </p>
        </div>

        <button wire:click="openCreate" type="button"
                class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer self-start sm:self-auto">
            + Add Player
        </button>
    </div>

    <div class="arena-card rounded-2xl px-4 py-3 flex flex-wrap items-center gap-3">
        <label class="text-xs font-semibold uppercase tracking-wider text-slate-300">Team Filter:</label>
        <select wire:model.live="selectedTeamId"
                class="rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2 text-sm text-white focus:border-orange-500 focus:outline-none">
            <option value="">All Teams</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}">{{ $team->name }} ({{ $team->tournament->name }})</option>
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
                    {{ $editingId ? 'Edit Player' : 'Add Player to Roster' }}
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Team</label>
                    <select wire:model="team_id"
                            class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        <option value="">Select team...</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} ({{ $team->tournament->name }})</option>
                        @endforeach
                    </select>
                    @error('team_id') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">First Name</label>
                    <input wire:model="first_name" type="text" placeholder="e.g., Jericho"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('first_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Last Name</label>
                    <input wire:model="last_name" type="text" placeholder="e.g., Alvarez"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('last_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Jersey Number (00 or 0–99)</label>
                    <input wire:model="jersey_number" type="text" maxlength="3" placeholder="e.g., 23"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('jersey_number') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Position</label>
                    <select wire:model="position"
                            class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->value }}">{{ $pos->label() }}</option>
                        @endforeach
                    </select>
                    @error('position') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Birthdate</label>
                    <input wire:model="birthdate" type="date"
                           class="w-full rounded-xl border border-white/10 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('birthdate') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-col justify-center gap-2 pt-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200 cursor-pointer">
                        <input wire:model="is_import" type="checkbox" class="rounded border-white/20 bg-[#06080d] text-orange-600">
                        Import Player (non-resident)
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-200 cursor-pointer">
                        <input wire:model="is_suspended" type="checkbox" class="rounded border-white/20 bg-[#06080d] text-rose-600">
                        Suspended
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button wire:click="cancel" type="button"
                        class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                        class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer">
                    {{ $editingId ? 'Save Changes' : 'Add Player' }}
                </button>
            </div>
        </form>
    @endif

    <div class="arena-card rounded-2xl overflow-x-auto">
        @if ($players->isEmpty())
            <div class="p-8 text-center text-sm text-slate-300">
                No players found for this selection. Click <strong class="text-orange-400">+ Add Player</strong> to build the team roster.
            </div>
        @else
            <table class="min-w-full divide-y divide-white/10 text-sm">
                <thead class="bg-black/40 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Jersey</th>
                        <th class="px-5 py-3.5 text-left">Player</th>
                        <th class="px-5 py-3.5 text-left">Team</th>
                        <th class="px-5 py-3.5 text-left">Pos</th>
                        <th class="px-5 py-3.5 text-left">Residency / Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach ($players as $player)
                        @php
                            $playerInitials = strtoupper(substr($player->first_name, 0, 1).substr($player->last_name, 0, 1));
                        @endphp
                        <tr wire:key="player-{{ $player->id }}" class="hover:bg-white/[0.03] transition">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-display text-2xl font-extrabold italic tracking-tight text-orange-400 drop-shadow-[0_0_8px_rgba(249,115,22,0.35)]">
                                    #{{ $player->jersey_number }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-orange-500/25 to-amber-500/10 border border-orange-500/35 text-xs font-bold text-orange-200">
                                        {{ $playerInitials }}
                                    </span>
                                    <div>
                                        <span class="font-semibold text-white">{{ $player->fullName }}</span>
                                        @if ($player->age() !== null)
                                            <span class="ml-1.5 text-xs text-slate-400">({{ $player->age() }} yrs)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-200 font-medium">{{ $player->team->name }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-lg border border-white/15 bg-white/5 px-2.5 py-0.5 font-display text-sm font-bold tracking-wider text-white">
                                    {{ $player->position->value }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 space-x-1.5 whitespace-nowrap">
                                @if ($player->is_import)
                                    <span class="inline-flex items-center rounded-full border border-sky-400/35 bg-sky-500/15 px-2.5 py-0.5 text-xs font-semibold text-sky-300 shadow-[0_0_12px_rgba(56,189,248,0.18)]">
                                        Import
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-emerald-400/35 bg-emerald-500/15 px-2.5 py-0.5 text-xs font-semibold text-emerald-300 shadow-[0_0_12px_rgba(52,211,153,0.18)]">
                                        Homegrown
                                    </span>
                                @endif
                                @if ($player->is_suspended)
                                    <span class="inline-flex items-center rounded-full border border-rose-500/40 bg-rose-500/15 px-2.5 py-0.5 text-xs font-semibold text-rose-300 shadow-[0_0_12px_rgba(244,63,94,0.2)]">
                                        Suspended
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2 whitespace-nowrap">
                                <button wire:click="edit({{ $player->id }})" type="button"
                                        class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-xs font-semibold text-slate-200 hover:bg-white/10 transition cursor-pointer">
                                    Edit
                                </button>
                                <button wire:click="delete({{ $player->id }})" wire:confirm="Remove this player from the roster?" type="button"
                                        class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition cursor-pointer">
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
