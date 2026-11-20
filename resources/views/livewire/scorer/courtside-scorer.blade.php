<div class="flex-1 min-h-0 flex flex-col gap-2.5 p-2.5 sm:p-3 lg:overflow-hidden"
     x-data="{
        seconds: 600,
        running: false,
        intervalId: null,
        isOnline: navigator.onLine,
        isSyncing: false,
        offlineQueue: [],
        storageKey: 'bh_offline_queue_{{ $game->id }}',

        entryMode: 'player_first',
        pendingStat: null,
        pendingStatLabel: '',

        foulMenuOpen: false,
        confirmDisq: false,
        adjustMenuOpen: false,
        shortcutsOpen: false,

        subTeamId: null,
        multiSub: false,
        subOutPlayerId: null,
        subOutLabel: '',
        batchOutIds: [],
        batchInIds: [],

        toastMessage: '',
        toastVisible: false,
        toastTimer: null,
        homeFlash: false,
        awayFlash: false,
        feedFlash: false,

        init() {
            const savedMode = localStorage.getItem('bh_scorer_entry_mode');
            if (savedMode === 'stat_first' || savedMode === 'player_first') {
                this.entryMode = savedMode;
            }
            const saved = localStorage.getItem(this.storageKey);
            if (saved) {
                try { this.offlineQueue = JSON.parse(saved) || []; } catch (e) { this.offlineQueue = []; }
            }
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.syncQueue();
            });
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });
            window.addEventListener('play-logged', (e) => {
                const detail = e.detail || {};
                if (detail.message) {
                    this.showToast(detail.message);
                }
                if (detail.teamSide === 'home' && detail.points > 0) {
                    this.homeFlash = true;
                    setTimeout(() => { this.homeFlash = false; }, 650);
                }
                if (detail.teamSide === 'away' && detail.points > 0) {
                    this.awayFlash = true;
                    setTimeout(() => { this.awayFlash = false; }, 650);
                }
                this.feedFlash = true;
                setTimeout(() => { this.feedFlash = false; }, 900);
            });
        },
        setEntryMode(mode) {
            this.entryMode = mode;
            this.pendingStat = null;
            this.pendingStatLabel = '';
            localStorage.setItem('bh_scorer_entry_mode', mode);
        },
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => { this.toastVisible = false; }, 2600);
        },
        toggleClock() {
            if (this.running) {
                clearInterval(this.intervalId);
                this.running = false;
            } else {
                this.running = true;
                this.intervalId = setInterval(() => {
                    if (this.seconds > 0) {
                        this.seconds--;
                    } else {
                        clearInterval(this.intervalId);
                        this.running = false;
                    }
                }, 1000);
            }
        },
        adjustClock(delta) {
            this.seconds = Math.max(0, Math.min(1200, this.seconds + delta));
        },
        resetClock() {
            clearInterval(this.intervalId);
            this.running = false;
            this.seconds = 600;
            this.adjustMenuOpen = false;
        },
        formattedClock() {
            const mins = String(Math.floor(this.seconds / 60)).padStart(2, '0');
            const secs = String(this.seconds % 60).padStart(2, '0');
            return `${mins}:${secs}`;
        },
        handleStatTap(eventType, label) {
            this.foulMenuOpen = false;
            this.confirmDisq = false;
            if (this.entryMode === 'stat_first' && eventType !== 'violation') {
                if (this.pendingStat === eventType) {
                    this.pendingStat = null;
                    this.pendingStatLabel = '';
                } else {
                    this.pendingStat = eventType;
                    this.pendingStatLabel = label;
                }
                return;
            }
            this.dispatchPlay(eventType);
        },
        handlePlayerCardTap(teamId, playerId, playerLabel) {
            // Sub Mode: Step 1 (Single) or Toggle Out (Multi)
            if (this.subTeamId === teamId) {
                if (this.multiSub) {
                    if (this.batchOutIds.includes(playerId)) {
                        this.batchOutIds = this.batchOutIds.filter((id) => id !== playerId);
                    } else {
                        this.batchOutIds = [...this.batchOutIds, playerId];
                    }
                    return;
                }
                this.subOutPlayerId = playerId;
                this.subOutLabel = playerLabel;
                return;
            }
            // Stat-First Mode: log pendingStat for tapped player
            if (this.entryMode === 'stat_first' && this.pendingStat) {
                const stat = this.pendingStat;
                const clock = this.formattedClock();
                this.pendingStat = null;
                this.pendingStatLabel = '';
                if (!this.isOnline) {
                    this.queueOfflineForPlayer(teamId, playerId, stat, clock);
                    return;
                }
                this.isSyncing = true;
                $wire.logPlayForPlayer(teamId, playerId, stat, clock)
                    .catch(() => this.queueOfflineForPlayer(teamId, playerId, stat, clock))
                    .finally(() => { this.isSyncing = false; });
                return;
            }
            $wire.selectPlayer(teamId, playerId);
        },
        toggleSubMode(teamId, isMulti = false) {
            if (this.subTeamId === teamId && this.multiSub === isMulti) {
                this.cancelSub();
                return;
            }
            this.subTeamId = teamId;
            this.multiSub = isMulti;
            this.subOutPlayerId = null;
            this.subOutLabel = '';
            this.batchOutIds = [];
            this.batchInIds = [];
        },
        cancelSub() {
            this.subTeamId = null;
            this.multiSub = false;
            this.subOutPlayerId = null;
            this.subOutLabel = '';
            this.batchOutIds = [];
            this.batchInIds = [];
        },
        async handleBenchTap(teamId, playerInId) {
            // Multi-Sub Mode: toggle In player
            if (this.subTeamId === teamId && this.multiSub) {
                if (this.batchInIds.includes(playerInId)) {
                    this.batchInIds = this.batchInIds.filter((id) => id !== playerInId);
                } else {
                    this.batchInIds = [...this.batchInIds, playerInId];
                }
                return;
            }
            // Single Sub Step 2
            if (this.subTeamId === teamId && this.subOutPlayerId) {
                await this.completeSubIn(teamId, playerInId);
                return;
            }
            // Quick Swap gesture: no Sub button needed when an on-court player on the same team is already selected
            this.isSyncing = true;
            try {
                await $wire.substitute(teamId, playerInId, this.formattedClock());
            } finally {
                this.isSyncing = false;
            }
        },
        async confirmBatchSub(teamId) {
            if (this.batchOutIds.length === 0 || this.batchOutIds.length !== this.batchInIds.length) return;
            const outIds = [...this.batchOutIds];
            const inIds = [...this.batchInIds];
            this.cancelSub();
            this.isSyncing = true;
            try {
                await $wire.swapBatch(teamId, outIds, inIds, this.formattedClock());
            } finally {
                this.isSyncing = false;
            }
        },
        async completeSubIn(teamId, playerInId) {
            if (this.subTeamId !== teamId || !this.subOutPlayerId) return;
            const outId = this.subOutPlayerId;
            this.cancelSub();
            this.isSyncing = true;
            try {
                await $wire.swapPlayers(teamId, outId, playerInId, this.formattedClock());
            } finally {
                this.isSyncing = false;
            }
        },
        dispatchPlay(eventType) {
            const clock = this.formattedClock();
            if (!this.isOnline) {
                this.queueOffline(eventType, clock);
                return;
            }
            this.isSyncing = true;
            $wire.logPlay(eventType, clock)
                .catch(() => {
                    this.queueOffline(eventType, clock);
                })
                .finally(() => {
                    this.isSyncing = false;
                });
        },
        queueOffline(eventType, clock) {
            const teamId = $wire.selectedTeamId;
            const playerId = $wire.selectedPlayerId;
            if (!teamId) return;
            this.queueOfflineForPlayer(teamId, playerId, eventType, clock);
        },
        queueOfflineForPlayer(teamId, playerId, eventType, clock) {
            this.offlineQueue.push({
                teamId: teamId,
                playerId: playerId,
                eventType: eventType,
                quarter: {{ $game->current_quarter }},
                gameClock: clock
            });
            localStorage.setItem(this.storageKey, JSON.stringify(this.offlineQueue));
            this.showToast('Queued offline: ' + eventType.toUpperCase());
        },
        async syncQueue() {
            if (!this.isOnline || this.offlineQueue.length === 0) return;
            this.isSyncing = true;
            const batch = [...this.offlineQueue];
            try {
                await $wire.flushOfflineQueue(batch);
                this.offlineQueue = [];
                localStorage.removeItem(this.storageKey);
            } finally {
                this.isSyncing = false;
            }
        },
        onKeydown(e) {
            if (['INPUT', 'SELECT', 'TEXTAREA'].includes(e.target.tagName)) return;
            const key = e.key.toLowerCase();
            if (e.code === 'Space') {
                e.preventDefault();
                this.toggleClock();
            } else if (key === '1') {
                this.handleStatTap('pt_1', '+1 FT');
            } else if (key === '2') {
                this.handleStatTap('pt_2', '+2 FG');
            } else if (key === '3') {
                this.handleStatTap('pt_3', '+3 FG');
            } else if (key === 'r') {
                this.handleStatTap('reb_def', 'DEF REB');
            } else if (key === 'o') {
                this.handleStatTap('reb_off', 'OFF REB');
            } else if (key === 'a') {
                this.handleStatTap('ast', 'AST');
            } else if (key === 's') {
                this.handleStatTap('stl', 'STL');
            } else if (key === 'b') {
                this.handleStatTap('blk', 'BLK');
            } else if (key === 't') {
                this.handleStatTap('to', 'TO');
            } else if (key === 'f') {
                this.foulMenuOpen = !this.foulMenuOpen;
                this.confirmDisq = false;
            } else if (key === 'z') {
                $wire.undoLastPlay();
            } else if (e.key === 'Escape') {
                this.foulMenuOpen = false;
                this.confirmDisq = false;
                this.adjustMenuOpen = false;
                this.shortcutsOpen = false;
                this.pendingStat = null;
                this.cancelSub();
            }
        }
     }"
     @keydown.window="onKeydown($event)">

    <!-- Instant Toast Notification -->
    <div x-show="toastVisible"
         x-transition
         style="display: none;"
         class="fixed bottom-4 right-4 z-50 rounded-2xl border border-orange-500/60 bg-[#0b1326]/95 px-4 py-3 text-sm font-bold text-white shadow-[0_12px_35px_rgba(249,115,22,0.35)] backdrop-blur-xl flex items-center gap-2.5">
        <span class="h-2.5 w-2.5 rounded-full bg-orange-400 animate-ping"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- COMPACT TOP BAR (Scorer Mode Header) -->
    <header class="relative z-30 shrink-0 arena-card rounded-2xl px-3.5 py-2 flex flex-wrap items-center justify-between gap-2.5">
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('scorer.index') }}" wire:navigate
               class="inline-flex items-center gap-1.5 rounded-xl border border-white/15 bg-white/5 px-3 py-1.5 text-[13px] font-semibold text-white hover:border-orange-500/50 transition">
                <span>&larr; Exit Scorer</span>
            </a>

            <div class="hidden sm:block leading-tight">
                <div class="text-[13px] font-bold text-orange-400">{{ $game->tournament->name }}</div>
                <div class="text-[13px] text-slate-300">{{ $game->venue ?? 'Barangay Covered Court' }}</div>
            </div>

            <!-- Entry Flow Mode Switch -->
            <div class="inline-flex rounded-xl border border-white/15 bg-[#06080d] p-0.5 text-[13px]">
                <button @click="setEntryMode('player_first')" type="button"
                        :class="entryMode === 'player_first' ? 'bg-orange-600 text-white font-bold' : 'text-slate-300 hover:text-white'"
                        class="rounded-lg px-2.5 py-1 transition cursor-pointer">
                    Player &rarr; Stat
                </button>
                <button @click="setEntryMode('stat_first')" type="button"
                        :class="entryMode === 'stat_first' ? 'bg-orange-600 text-white font-bold' : 'text-slate-300 hover:text-white'"
                        class="rounded-lg px-2.5 py-1 transition cursor-pointer">
                    Stat &rarr; Player
                </button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Visible Saved / Syncing / Offline Status Indicator -->
            <div class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-[13px] font-semibold"
                 :class="!isOnline || offlineQueue.length > 0
                    ? 'border-amber-400/50 bg-amber-500/15 text-amber-200'
                    : (isSyncing ? 'border-sky-400/50 bg-sky-500/15 text-sky-200' : 'border-emerald-500/40 bg-emerald-500/15 text-emerald-200')">
                <span class="h-2 w-2 rounded-full"
                      :class="!isOnline || offlineQueue.length > 0 ? 'bg-amber-400 animate-pulse' : (isSyncing ? 'bg-sky-400 animate-ping' : 'bg-emerald-400')"></span>
                <span x-text="!isOnline ? 'Offline (' + offlineQueue.length + ' queued)' : (isSyncing ? 'Syncing...' : (offlineQueue.length > 0 ? 'Syncing ' + offlineQueue.length + '...' : 'Saved'))"></span>
                <button x-show="isOnline && offlineQueue.length > 0" @click="syncQueue()" type="button" class="underline text-white cursor-pointer">Sync</button>
            </div>

            <!-- Keyboard Shortcuts Popover -->
            <div class="relative">
                <button @click="shortcutsOpen = !shortcutsOpen" type="button"
                        class="rounded-xl border border-white/15 bg-white/5 px-2.5 py-1.5 text-[13px] font-bold text-slate-200 hover:bg-white/10 cursor-pointer"
                        title="Keyboard Shortcuts">
                    ? Keys
                </button>
                <div x-show="shortcutsOpen" @click.away="shortcutsOpen = false" style="display: none;"
                     class="absolute right-0 mt-2 w-64 z-50 rounded-2xl border border-orange-500/45 bg-[#0c1120] p-3.5 text-[13px] text-slate-200 space-y-1.5 shadow-[0_20px_50px_rgba(0,0,0,0.85)]">
                    <div class="font-bold text-orange-300 uppercase tracking-wider">Keyboard Shortcuts</div>
                    <div class="flex justify-between"><span>Start / Pause Clock</span><kbd class="px-1.5 rounded bg-white/10 font-mono">Space</kbd></div>
                    <div class="flex justify-between"><span>+1 FT / +2 FG / +3 FG</span><kbd class="px-1.5 rounded bg-white/10 font-mono">1 / 2 / 3</kbd></div>
                    <div class="flex justify-between"><span>DEF / OFF Rebound</span><kbd class="px-1.5 rounded bg-white/10 font-mono">R / O</kbd></div>
                    <div class="flex justify-between"><span>AST / STL / BLK / TO</span><kbd class="px-1.5 rounded bg-white/10 font-mono">A / S / B / T</kbd></div>
                    <div class="flex justify-between"><span>Foul Popup / Undo</span><kbd class="px-1.5 rounded bg-white/10 font-mono">F / Z</kbd></div>
                </div>
            </div>

            <a href="{{ route('live.game', $game) }}" target="_blank"
               class="rounded-xl border border-white/15 bg-white/5 px-3 py-1.5 text-[13px] font-semibold text-slate-100 hover:bg-white/10">
                Live ↗
            </a>

            @if ($game->status === \App\Enums\GameStatus::Scheduled)
                <button wire:click="startGame" type="button"
                        class="btn-hoops-primary rounded-xl px-3.5 py-1.5 text-[13px] font-bold text-white cursor-pointer">
                    Tip-Off
                </button>
            @endif

            <button wire:click="openEndGameModal" type="button"
                    class="rounded-xl px-3.5 py-1.5 text-[13px] font-bold transition cursor-pointer {{ $game->current_quarter >= 4 ? 'btn-hoops-primary text-white' : 'border border-white/20 bg-white/10 text-white hover:border-orange-400/50' }}">
                End Game
            </button>
        </div>
    </header>

    @if ($errorBanner)
        <div class="shrink-0 rounded-xl border border-amber-500/45 bg-amber-500/15 px-4 py-2 text-sm font-medium text-amber-100">
            {{ $errorBanner }}
        </div>
    @endif

    <!-- SCOREBOARD & HERO CLOCK STRIP -->
    <section class="relative z-20 shrink-0 arena-card rounded-2xl px-4 py-3">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 items-center">
            <!-- Home Score -->
            <div class="lg:col-span-4 flex items-center justify-between gap-3 rounded-xl px-2 py-1 transition duration-300"
                 :class="homeFlash ? 'bg-orange-500/25 ring-2 ring-orange-400' : ''">
                <div class="min-w-0">
                    <div class="font-display text-2xl sm:text-3xl font-extrabold text-white leading-tight truncate">
                        {{ $game->homeTeam->name }}
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-[13px] text-slate-200 mt-0.5">
                        <span>Fouls: <strong>{{ $periodSummary['current_quarter_fouls']['home'] }}</strong>/5</span>
                        @if ($periodSummary['home_penalty'])
                            <span class="rounded-md bg-rose-500/25 border border-rose-400/60 px-2 py-0.5 text-xs font-extrabold text-rose-200 animate-pulse">BONUS</span>
                        @endif
                        <span>TO: <strong class="text-amber-300">{{ $periodSummary['timeouts_remaining']['home'] }}</strong></span>
                        <button @click="$wire.callTimeout({{ $game->home_team_id }}, formattedClock())" type="button"
                                class="rounded-lg border border-white/15 bg-white/10 px-2 py-0.5 text-xs font-bold text-white hover:bg-orange-500/30 cursor-pointer">
                            +TO
                        </button>
                    </div>
                </div>
                <x-flip-score :score="$game->home_score" accent="orange" size="md" />
            </div>

            <!-- Hero Game Clock + Quarter Controls -->
            <div class="lg:col-span-4 flex flex-col items-center justify-center gap-1.5 border-y lg:border-y-0 lg:border-x border-white/10 py-2 lg:py-0 lg:px-3">
                <div class="flex flex-wrap items-center justify-center gap-1">
                    @foreach ([1 => 'Q1', 2 => 'Q2', 3 => 'Q3', 4 => 'Q4', 5 => 'OT1', 6 => 'OT2'] as $qNum => $qLabel)
                        <button wire:click="changeQuarter({{ $qNum }})" type="button"
                                class="rounded-lg px-2.5 py-0.5 text-[13px] font-bold transition cursor-pointer {{ $game->current_quarter === $qNum ? 'bg-orange-600 text-white shadow-[0_0_12px_rgba(249,115,22,0.5)]' : 'bg-white/5 text-slate-200 hover:bg-white/10' }}">
                            {{ $qLabel }}
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-3">
                    <div class="font-display text-5xl sm:text-6xl font-extrabold tracking-wider text-white tabular-nums leading-none" x-text="formattedClock()"></div>

                    <button @click="toggleClock()" type="button"
                            class="min-h-[48px] rounded-xl px-5 py-2 text-sm font-extrabold uppercase tracking-wider text-white shadow-lg transition cursor-pointer"
                            :class="running ? 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/30' : 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/30'"
                            x-text="running ? 'Stop' : 'Start'"></button>

                    <div class="relative">
                        <button @click="adjustMenuOpen = !adjustMenuOpen" type="button"
                                class="rounded-xl border border-white/15 bg-white/5 px-2.5 py-2 text-[13px] font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                            Adjust &#9662;
                        </button>
                        <div x-show="adjustMenuOpen" @click.away="adjustMenuOpen = false" style="display: none;"
                             class="absolute right-0 mt-1.5 w-40 z-50 rounded-xl border border-white/20 bg-[#0c1120] p-1.5 space-y-1 text-[13px] shadow-[0_20px_50px_rgba(0,0,0,0.85)]">
                            <button @click="adjustClock(-10)" type="button" class="w-full text-left rounded-lg px-2.5 py-1.5 text-white hover:bg-white/10 cursor-pointer">-10 Seconds</button>
                            <button @click="adjustClock(10)" type="button" class="w-full text-left rounded-lg px-2.5 py-1.5 text-white hover:bg-white/10 cursor-pointer">+10 Seconds</button>
                            <button @click="adjustClock(-1)" type="button" class="w-full text-left rounded-lg px-2.5 py-1.5 text-white hover:bg-white/10 cursor-pointer">-1 Second</button>
                            <button @click="resetClock()" type="button" class="w-full text-left rounded-lg px-2.5 py-1.5 text-orange-300 hover:bg-white/10 cursor-pointer">Reset 10:00</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Away Score -->
            <div class="lg:col-span-4 flex items-center justify-between gap-3 rounded-xl px-2 py-1 transition duration-300"
                 :class="awayFlash ? 'bg-orange-500/25 ring-2 ring-orange-400' : ''">
                <x-flip-score :score="$game->away_score" accent="white" size="md" class="order-2 lg:order-1" />
                <div class="min-w-0 lg:text-right order-1 lg:order-2">
                    <div class="font-display text-2xl sm:text-3xl font-extrabold text-white leading-tight truncate">
                        {{ $game->awayTeam->name }}
                    </div>
                    <div class="flex flex-wrap items-center lg:justify-end gap-2 text-[13px] text-slate-200 mt-0.5">
                        <span>Fouls: <strong>{{ $periodSummary['current_quarter_fouls']['away'] }}</strong>/5</span>
                        @if ($periodSummary['away_penalty'])
                            <span class="rounded-md bg-rose-500/25 border border-rose-400/60 px-2 py-0.5 text-xs font-extrabold text-rose-200 animate-pulse">BONUS</span>
                        @endif
                        <span>TO: <strong class="text-amber-300">{{ $periodSummary['timeouts_remaining']['away'] }}</strong></span>
                        <button @click="$wire.callTimeout({{ $game->away_team_id }}, formattedClock())" type="button"
                                class="rounded-lg border border-white/15 bg-white/10 px-2 py-0.5 text-xs font-bold text-white hover:bg-orange-500/30 cursor-pointer">
                            +TO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN 3-COLUMN SINGLE-SCREEN CONSOLE -->
    <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-12 gap-2.5">
        <!-- HOME TEAM ROSTER & BENCH PANEL -->
        @include('livewire.scorer.partials.team-roster-panel', [
            'team' => $game->homeTeam,
            'onCourt' => $homeOnCourt,
            'bench' => $homeBench,
        ])

        <!-- CENTER COLUMN: ACTION PAD + LIVE FEED -->
        <section class="lg:col-span-6 flex flex-col min-h-0 gap-2.5">
            <!-- Action Pad Card -->
            <div class="arena-card rounded-2xl p-3.5 space-y-2.5 shrink-0 relative z-10">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="text-sm font-semibold text-slate-200">
                        <template x-if="entryMode === 'stat_first'">
                            <span>
                                <span x-show="!pendingStat" class="text-orange-300 font-bold">Stat-First Mode: Tap a stat button below, then tap a player.</span>
                                <span x-show="pendingStat" class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-2.5 py-1 text-white font-bold animate-pulse">
                                    Tap player for: <span x-text="pendingStatLabel"></span>
                                </span>
                            </span>
                        </template>
                        <template x-if="entryMode === 'player_first'">
                            <span>
                                Active:
                                @if ($selectedPlayer)
                                    <strong class="text-orange-400">#{{ $selectedPlayer->jersey_number }}</strong>
                                    <strong class="text-white">{{ $selectedPlayer->fullName }}</strong>
                                @else
                                    <span class="text-amber-300">Tap an on-court player</span>
                                @endif
                            </span>
                        </template>
                    </div>

                    <button wire:click="undoLastPlay" type="button"
                            class="rounded-xl border border-amber-400/50 bg-amber-500/15 px-3.5 py-1.5 text-[13px] font-bold text-amber-200 hover:bg-amber-500/25 transition cursor-pointer">
                        &#8634; Undo (Z)
                    </button>
                </div>

                <!-- Scoring & Split Misses (min-h-[60px]) -->
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <button @click="handleStatTap('pt_1', '+1 FT')" type="button"
                            :class="pendingStat === 'pt_1' ? 'ring-2 ring-white scale-[1.02]' : ''"
                            class="min-h-[60px] rounded-xl bg-emerald-600 hover:bg-emerald-500 font-display text-2xl font-extrabold text-white shadow cursor-pointer transition">
                        +1 FT
                    </button>
                    <button @click="handleStatTap('pt_2', '+2 FG')" type="button"
                            :class="pendingStat === 'pt_2' ? 'ring-2 ring-white scale-[1.02]' : ''"
                            class="min-h-[60px] rounded-xl bg-orange-600 hover:bg-orange-500 font-display text-2xl font-extrabold text-white shadow cursor-pointer transition">
                        +2 FG
                    </button>
                    <button @click="handleStatTap('pt_3', '+3 FG')" type="button"
                            :class="pendingStat === 'pt_3' ? 'ring-2 ring-white scale-[1.02]' : ''"
                            class="min-h-[60px] rounded-xl bg-amber-500 hover:bg-amber-400 font-display text-2xl font-extrabold text-slate-950 shadow cursor-pointer transition">
                        +3 FG
                    </button>
                    <button @click="handleStatTap('miss_1', 'FT Miss')" type="button"
                            :class="pendingStat === 'miss_1' ? 'ring-2 ring-orange-400' : ''"
                            class="min-h-[60px] rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-[13px] font-bold text-slate-200 cursor-pointer transition">
                        FT Miss
                    </button>
                    <button @click="handleStatTap('miss_2', '2PT Miss')" type="button"
                            :class="pendingStat === 'miss_2' ? 'ring-2 ring-orange-400' : ''"
                            class="min-h-[60px] rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-[13px] font-bold text-slate-200 cursor-pointer transition">
                        2PT Miss
                    </button>
                    <button @click="handleStatTap('miss_3', '3PT Miss')" type="button"
                            :class="pendingStat === 'miss_3' ? 'ring-2 ring-orange-400' : ''"
                            class="min-h-[60px] rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-[13px] font-bold text-slate-200 cursor-pointer transition">
                        3PT Miss
                    </button>
                </div>

                <!-- Hustle Stats with Distinct Colors & Icons (min-h-[60px]) -->
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                    <button @click="handleStatTap('reb_off', 'OFF REB')" type="button"
                            :class="pendingStat === 'reb_off' ? 'ring-2 ring-white' : ''"
                            class="min-h-[60px] rounded-xl border border-sky-400/40 bg-sky-500/20 hover:bg-sky-500/30 flex flex-col items-center justify-center gap-0.5 text-[13px] font-bold text-sky-100 cursor-pointer transition">
                        <svg class="h-4 w-4 text-sky-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5m0 0-5 5m5-5 5 5"/></svg>
                        <span>OFF REB</span>
                    </button>
                    <button @click="handleStatTap('reb_def', 'DEF REB')" type="button"
                            :class="pendingStat === 'reb_def' ? 'ring-2 ring-white' : ''"
                            class="min-h-[60px] rounded-xl border border-cyan-400/40 bg-cyan-500/20 hover:bg-cyan-500/30 flex flex-col items-center justify-center gap-0.5 text-[13px] font-bold text-cyan-100 cursor-pointer transition">
                        <svg class="h-4 w-4 text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14m0 0-5-5m5 5 5-5"/></svg>
                        <span>DEF REB</span>
                    </button>
                    <button @click="handleStatTap('ast', 'AST')" type="button"
                            :class="pendingStat === 'ast' ? 'ring-2 ring-white' : ''"
                            class="min-h-[60px] rounded-xl border border-indigo-400/45 bg-indigo-500/25 hover:bg-indigo-500/35 flex flex-col items-center justify-center gap-0.5 text-[13px] font-bold text-indigo-100 cursor-pointer transition">
                        <svg class="h-4 w-4 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14m0 0-5-5m5 5-5 5"/></svg>
                        <span>AST</span>
                    </button>
                    <button @click="handleStatTap('stl', 'STL')" type="button"
                            :class="pendingStat === 'stl' ? 'ring-2 ring-white' : ''"
                            class="min-h-[60px] rounded-xl border border-teal-400/45 bg-teal-500/25 hover:bg-teal-500/35 flex flex-col items-center justify-center gap-0.5 text-[13px] font-bold text-teal-100 cursor-pointer transition">
                        <svg class="h-4 w-4 text-teal-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/></svg>
                        <span>STL</span>
                    </button>
                    <button @click="handleStatTap('blk', 'BLK')" type="button"
                            :class="pendingStat === 'blk' ? 'ring-2 ring-white' : ''"
                            class="min-h-[60px] rounded-xl border border-purple-400/45 bg-purple-500/25 hover:bg-purple-500/35 flex flex-col items-center justify-center gap-0.5 text-[13px] font-bold text-purple-100 cursor-pointer transition">
                        <svg class="h-4 w-4 text-purple-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3l8 4v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V7l8-4z"/></svg>
                        <span>BLK</span>
                    </button>
                    <button @click="handleStatTap('to', 'TO')" type="button"
                            :class="pendingStat === 'to' ? 'ring-2 ring-white' : ''"
                            class="min-h-[60px] rounded-xl border border-amber-400/45 bg-amber-600/25 hover:bg-amber-600/35 flex flex-col items-center justify-center gap-0.5 text-[13px] font-bold text-amber-100 cursor-pointer transition">
                        <svg class="h-4 w-4 text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01"/></svg>
                        <span>TO</span>
                    </button>
                </div>

                <!-- Merged FOUL Button (with Popup) + Violation -->
                <div class="grid grid-cols-2 gap-2 relative">
                    <div class="relative">
                        <button @click="foulMenuOpen = !foulMenuOpen; confirmDisq = false" type="button"
                                class="w-full min-h-[56px] rounded-xl border border-rose-500/50 bg-rose-600/25 hover:bg-rose-600/35 flex items-center justify-center gap-2 text-sm font-extrabold uppercase tracking-wider text-rose-100 cursor-pointer transition">
                            <svg class="h-5 w-5 text-rose-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                            <span>Foul (F) &#9662;</span>
                        </button>

                        <!-- Foul Type Popup -->
                        <div x-show="foulMenuOpen" @click.away="foulMenuOpen = false; confirmDisq = false" style="display: none;"
                             class="absolute left-0 right-0 bottom-16 z-50 rounded-2xl border border-rose-500/55 bg-[#0c1120] p-2.5 space-y-1.5 shadow-[0_20px_50px_rgba(0,0,0,0.85)]">
                            <button @click="handleStatTap('foul_p', 'Personal Foul')" type="button"
                                    class="w-full text-left rounded-xl bg-white/5 hover:bg-rose-500/25 px-3.5 py-2.5 text-sm font-bold text-white cursor-pointer">
                                Personal Foul (P)
                            </button>
                            <button @click="handleStatTap('foul_t', 'Technical Foul (T)')" type="button"
                                    class="w-full text-left rounded-xl bg-white/5 hover:bg-rose-500/25 px-3.5 py-2.5 text-sm font-bold text-amber-200 cursor-pointer">
                                Technical Foul (T)
                            </button>
                            <button @click="handleStatTap('foul_u', 'Unsportsmanlike (U)')" type="button"
                                    class="w-full text-left rounded-xl bg-white/5 hover:bg-rose-500/25 px-3.5 py-2.5 text-sm font-bold text-orange-200 cursor-pointer">
                                Unsportsmanlike Foul (U)
                            </button>

                            <template x-if="!confirmDisq">
                                <button @click="confirmDisq = true" type="button"
                                        class="w-full text-left rounded-xl bg-rose-600/25 hover:bg-rose-600/40 px-3.5 py-2.5 text-sm font-bold text-rose-200 cursor-pointer">
                                    Disqualifying Foul (D)...
                                </button>
                            </template>
                            <template x-if="confirmDisq">
                                <div class="rounded-xl border border-rose-400 bg-rose-950/90 p-2.5 space-y-2">
                                    <div class="text-[13px] font-bold text-rose-100">Confirm Ejection (Disqualifying Foul)?</div>
                                    <div class="flex gap-2">
                                        <button @click="handleStatTap('foul_d', 'Disqualifying (D)')" type="button"
                                                class="flex-1 rounded-lg bg-rose-600 py-1.5 text-xs font-extrabold text-white cursor-pointer">
                                            Confirm (D)
                                        </button>
                                        <button @click="confirmDisq = false" type="button"
                                                class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white cursor-pointer">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button @click="handleStatTap('violation', 'Violation')" type="button"
                            class="min-h-[56px] rounded-xl border border-white/15 bg-white/5 hover:bg-white/10 text-sm font-bold text-slate-100 cursor-pointer transition">
                        Violation (24s / 8s / Travel)
                    </button>
                </div>
            </div>

            <!-- Play-by-Play Feed -->
            <div class="flex-1 min-h-0 arena-card rounded-2xl overflow-hidden flex flex-col">
                <div class="px-4 py-2.5 border-b border-white/10 flex items-center justify-between shrink-0">
                    <span class="font-display text-xl font-bold uppercase text-orange-400">Play-by-Play Feed</span>
                    <span class="text-[13px] text-slate-200">Latest {{ $events->count() }} plays</span>
                </div>
                <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-white/10">
                    @forelse ($events as $index => $event)
                        <div class="px-4 py-2.5 flex items-center justify-between gap-3 text-[13px] sm:text-sm transition-colors duration-700"
                             :class="{{ $index === 0 ? 'feedFlash' : 'false' }} ? 'bg-orange-500/20' : ''">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="rounded-md bg-white/10 px-2 py-0.5 text-[13px] font-bold text-orange-300 shrink-0">
                                    {{ \App\Services\GameScoringService::quarterLabel($event->quarter) }} {{ $event->game_clock ?? '' }}
                                </span>
                                <span class="font-bold text-white shrink-0">{{ $event->event_type->label() }}</span>
                                @if ($event->player)
                                    <span class="text-slate-200 truncate">#{{ $event->player->jersey_number }} {{ $event->player->fullName }}</span>
                                @endif
                            </div>
                            <span class="text-[13px] font-medium text-slate-300 shrink-0">{{ $event->team->name }}</span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-slate-300">No plays recorded yet. Tap a stat to begin logging.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- AWAY TEAM ROSTER & BENCH PANEL -->
        @include('livewire.scorer.partials.team-roster-panel', [
            'team' => $game->awayTeam,
            'onCourt' => $awayOnCourt,
            'bench' => $awayBench,
        ])
    </div>

    <!-- END GAME & BPG MODAL -->
    @if ($showEndGameModal)
        <div class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="arena-card rounded-2xl border-orange-500/45 max-w-lg w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
                        <h2 class="font-display text-2xl font-bold uppercase text-white">End Game &amp; Crown BPG</h2>
                    </div>
                    <button wire:click="closeEndGameModal" type="button" class="text-slate-300 hover:text-white text-sm font-bold cursor-pointer">✕</button>
                </div>

                <div class="rounded-xl border border-white/10 bg-black/40 p-4 flex items-center justify-between text-center">
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-slate-300">{{ $game->homeTeam->name }}</div>
                        <div class="font-display text-4xl font-extrabold text-orange-400">{{ $game->home_score }}</div>
                    </div>
                    <div class="px-4 font-display text-xl font-bold text-slate-400">FINAL</div>
                    <div class="flex-1">
                        <div class="text-xs font-semibold text-slate-300">{{ $game->awayTeam->name }}</div>
                        <div class="font-display text-4xl font-extrabold text-white">{{ $game->away_score }}</div>
                    </div>
                </div>

                <p class="text-sm text-slate-300">
                    Best Player of the Game (BPG) is automatically computed using
                    <span class="text-orange-300 font-semibold">EFF = PTS + REB + AST + STL + BLK &minus; TO &minus; PF</span>.
                </p>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Manual BPG Override (Optional)</label>
                    <select wire:model="manualBestPlayerId"
                            class="w-full rounded-xl border border-white/15 bg-[#06080d] px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        <option value="">Auto-calculate highest EFF on winning team</option>
                        @foreach ($boxScores as $row)
                            <option value="{{ $row['player']->id }}">
                                #{{ $row['player']->jersey_number }} {{ $row['player']->fullName }} ({{ $row['pts'] }} PTS, {{ $row['eff'] }} EFF)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-1">
                    <button wire:click="closeEndGameModal" type="button"
                            class="rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-white/10 cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="finalizeGame" type="button"
                            class="btn-hoops-primary rounded-xl px-5 py-2.5 text-sm font-bold text-white cursor-pointer">
                        {{ $isFinished ? 'Update Final Result & BPG' : 'Finalize Game & Crown BPG' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
