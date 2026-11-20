@php
    $teamId = $team->id;
    $teamOnCourt = $onCourt;
    $teamBench = $bench;
    $activeOnCourtSelected = $selectedTeamId === $teamId && $selectedPlayerId !== null;
@endphp

<aside class="lg:col-span-3 arena-card rounded-2xl overflow-hidden flex flex-col min-h-0">
    <div class="h-1.5 w-full shrink-0" style="background-color: {{ $team->primary_color }}"></div>

    <!-- Team Header + Sub / Multi-Sub Trigger -->
    <div class="px-3.5 py-2.5 border-b border-white/10 flex items-center justify-between gap-2 shrink-0">
        <div class="min-w-0">
            <div class="font-display text-xl font-bold text-white leading-none truncate">{{ $team->name }}</div>
            <div class="text-[13px] text-slate-300 mt-0.5">
                On Court: <strong class="text-white">{{ $teamOnCourt->count() }}/5</strong> &bull; Bench: {{ $teamBench->count() }}
            </div>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button @click="toggleSubMode({{ $teamId }}, false)" type="button"
                    :class="subTeamId === {{ $teamId }} && !multiSub ? 'bg-orange-600 text-white border-orange-400' : 'bg-orange-500/15 text-orange-200 border-orange-500/40 hover:bg-orange-500/25'"
                    class="rounded-xl border px-3 py-1.5 text-[13px] font-bold transition cursor-pointer">
                <span x-text="subTeamId === {{ $teamId }} && !multiSub ? 'Cancel' : 'Sub'"></span>
            </button>
            <button @click="toggleSubMode({{ $teamId }}, true)" type="button"
                    :class="subTeamId === {{ $teamId }} && multiSub ? 'bg-amber-500 text-slate-950 border-amber-300' : 'bg-white/5 text-slate-200 border-white/15 hover:bg-white/10'"
                    class="rounded-xl border px-2.5 py-1.5 text-[13px] font-bold transition cursor-pointer"
                    title="Substitute multiple players at once">
                Multi
            </button>
        </div>
    </div>

    <!-- Two-Step Sub Progress Bar / Multi-Sub Bar -->
    <div x-show="subTeamId === {{ $teamId }}" style="display: none;"
         class="shrink-0 bg-[#141c33] border-b border-orange-500/40 px-3 py-2.5 space-y-2">
        <template x-if="!multiSub">
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-2 text-[13px] font-bold">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="rounded-md px-2 py-0.5"
                              :class="subOutPlayerId ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/40' : 'bg-orange-600 text-white'">
                            1 Out: <span x-text="subOutPlayerId ? subOutLabel : 'choose on-court'"></span>
                        </span>
                        <span class="text-slate-300">&rarr;</span>
                        <span class="rounded-md px-2 py-0.5"
                              :class="subOutPlayerId ? 'bg-orange-600 text-white animate-pulse' : 'bg-white/10 text-slate-300'">
                            2 In: choose bench player
                        </span>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button @click="cancelSub()" type="button"
                            class="w-full sm:w-auto min-h-[38px] rounded-xl border border-white/20 bg-white/10 px-4 py-1.5 text-[13px] font-bold text-white hover:bg-white/20 cursor-pointer">
                        Cancel Sub (Esc)
                    </button>
                </div>
            </div>
        </template>

        <template x-if="multiSub">
            <div class="space-y-2">
                <div class="text-[13px] font-bold text-amber-200">
                    Multi-Sub: <span class="text-white" x-text="batchOutIds.length"></span> Out &bull; <span class="text-white" x-text="batchInIds.length"></span> In
                </div>
                <div class="flex items-center gap-2">
                    <button @click="confirmBatchSub({{ $teamId }})"
                            type="button"
                            :disabled="batchOutIds.length === 0 || batchOutIds.length !== batchInIds.length"
                            :class="batchOutIds.length > 0 && batchOutIds.length === batchInIds.length ? 'btn-hoops-primary text-white' : 'bg-white/10 text-slate-400 cursor-not-allowed'"
                            class="flex-1 min-h-[38px] rounded-xl px-3 py-1.5 text-[13px] font-bold transition cursor-pointer">
                        Confirm <span x-text="batchOutIds.length || ''"></span> Subs
                    </button>
                    <button @click="cancelSub()" type="button"
                            class="min-h-[38px] rounded-xl border border-white/20 bg-white/10 px-3 py-1.5 text-[13px] font-bold text-white hover:bg-white/20 cursor-pointer">
                        Cancel (Esc)
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto p-2.5 space-y-2">
        <!-- ON COURT SECTION -->
        <div class="flex items-center justify-between px-1">
            <span class="inline-flex items-center gap-1.5 text-[13px] font-extrabold uppercase tracking-wider text-emerald-300">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                On Court ({{ $teamOnCourt->count() }}/5)
            </span>
            @if ($activeOnCourtSelected)
                <span class="text-xs text-slate-300">Tap bench to swap</span>
            @endif
        </div>

        <div class="space-y-1.5">
            @forelse ($teamOnCourt as $roster)
                @php
                    $isSelected = $selectedPlayerId === $roster->player_id;
                    $isFourFouls = $roster->fouls_count === 4;
                    $rotationLabel = $rotations[$roster->player_id] ?? ($roster->is_starting ? 'Starter' : 'On Court');
                @endphp
                <button @click="handlePlayerCardTap({{ $teamId }}, {{ $roster->player_id }}, '#{{ $roster->player->jersey_number }} {{ $roster->player->last_name }}')"
                        type="button"
                        :class="{
                            'ring-2 ring-amber-400 border-amber-400 bg-amber-500/20': (subTeamId === {{ $teamId }} && !multiSub && subOutPlayerId === {{ $roster->player_id }}) || (subTeamId === {{ $teamId }} && multiSub && batchOutIds.includes({{ $roster->player_id }})),
                            'opacity-35 pointer-events-none': subTeamId === {{ $teamId }} && !multiSub && subOutPlayerId && subOutPlayerId !== {{ $roster->player_id }},
                            'hover:ring-2 hover:ring-orange-400': entryMode === 'stat_first' && pendingStat
                        }"
                        class="w-full text-left rounded-xl p-2.5 border transition cursor-pointer {{ $isFourFouls ? 'border-rose-500 bg-rose-500/25' : ($isSelected ? 'border-orange-500 bg-orange-500/20 shadow-[0_0_16px_rgba(249,115,22,0.25)]' : 'border-white/10 bg-white/[0.04] hover:border-white/25') }}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-display text-2xl font-extrabold italic text-orange-400 leading-none">#{{ $roster->player->jersey_number }}</span>
                            <span class="text-sm font-bold text-white truncate">{{ $roster->player->fullName }}</span>
                        </div>
                        <span class="text-[13px] font-bold text-slate-200 shrink-0">{{ $roster->player->position->value }}</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between gap-2 text-[13px]">
                        <div class="flex items-center gap-1">
                            @for ($f = 1; $f <= 5; $f++)
                                <span class="h-2.5 w-2.5 rounded-full {{ $roster->fouls_count >= $f ? ($f >= 4 ? 'bg-rose-500' : 'bg-amber-400') : 'bg-white/20' }}"></span>
                            @endfor
                            <span class="ml-1 {{ $isFourFouls ? 'text-rose-200 font-extrabold' : 'text-slate-200 font-medium' }}">
                                {{ $roster->fouls_count }} PF{{ $isFourFouls ? ' · WARN' : '' }}
                            </span>
                        </div>
                        <span class="text-xs font-medium text-slate-300 shrink-0">{{ $rotationLabel }}</span>
                    </div>
                </button>
            @empty
                <div class="rounded-xl border border-white/10 bg-black/30 p-3 text-[13px] text-slate-300">
                    No players on court.
                </div>
            @endforelse
        </div>

        <!-- SUBTLE DIVIDER & BENCH SECTION -->
        <div class="border-t-2 border-dashed border-white/15 pt-2.5 mt-2 space-y-1.5">
            <div class="flex items-center justify-between px-1">
                <span class="text-[13px] font-extrabold uppercase tracking-wider text-slate-300">
                    Bench ({{ $teamBench->count() }})
                </span>
            </div>

            @forelse ($teamBench as $roster)
                @php
                    $isFourFouls = $roster->fouls_count === 4;
                    $isBlocked = $roster->is_ejected || $roster->player->is_suspended;
                    $rotationLabel = $rotations[$roster->player_id] ?? 'Bench';
                @endphp
                <div class="flex items-center justify-between gap-2 rounded-xl border px-2.5 py-2 text-[13px] transition {{ $isBlocked ? 'border-rose-500/40 bg-rose-950/30 opacity-60' : ($isFourFouls ? 'border-rose-500/70 bg-rose-500/15' : 'border-white/10 bg-black/40') }}"
                     :class="{
                        'ring-2 ring-emerald-400 border-emerald-400 bg-emerald-500/15': subTeamId === {{ $teamId }} && multiSub && batchInIds.includes({{ $roster->player_id }}),
                        'ring-1 ring-orange-400/70 border-orange-500/60 bg-orange-500/10': subTeamId === {{ $teamId }} && !multiSub && subOutPlayerId && !{{ $isBlocked ? 'true' : 'false' }}
                     }">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-display text-lg font-extrabold italic text-orange-400 leading-none">#{{ $roster->player->jersey_number }}</span>
                            <span class="font-semibold text-white truncate">{{ $roster->player->last_name }}</span>
                            <span class="text-slate-300 text-xs">({{ $roster->fouls_count }} PF)</span>
                        </div>
                        <div class="text-xs text-slate-300 mt-0.5">{{ $rotationLabel }}</div>
                    </div>

                    @if ($roster->is_ejected)
                        <span class="shrink-0 rounded-md bg-rose-600/35 border border-rose-500/60 px-2 py-0.5 text-xs font-extrabold text-rose-200">FOUL OUT</span>
                    @elseif ($roster->player->is_suspended)
                        <span class="shrink-0 rounded-md bg-rose-600/35 border border-rose-500/60 px-2 py-0.5 text-xs font-extrabold text-rose-200">SUSP</span>
                    @else
                        <button @click="handleBenchTap({{ $teamId }}, {{ $roster->player_id }})"
                                type="button"
                                :class="(subTeamId === {{ $teamId }} && multiSub && batchInIds.includes({{ $roster->player_id }}))
                                    ? 'bg-emerald-600 text-white border-emerald-400'
                                    : (subTeamId === {{ $teamId }} && !multiSub && subOutPlayerId ? 'bg-orange-600 text-white border-orange-400' : 'bg-white/10 text-orange-200 border-orange-500/35 hover:bg-orange-500/25')"
                                class="shrink-0 rounded-lg border px-2.5 py-1.5 text-xs font-bold cursor-pointer transition">
                            <span x-text="subTeamId === {{ $teamId }} && multiSub ? (batchInIds.includes({{ $roster->player_id }}) ? 'Selected In' : 'Select In') : 'Sub In'"></span>
                        </button>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-white/10 bg-black/30 p-3 text-[13px] text-slate-300">
                    No bench reserves.
                </div>
            @endforelse
        </div>
    </div>
</aside>
