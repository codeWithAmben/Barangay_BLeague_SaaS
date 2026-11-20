<x-layouts::guest title="Barangay Hoops — Barangay Basketball League SaaS">
    <!-- HERO SECTION -->
    <section id="home" class="relative overflow-hidden bg-[#050811] border-b border-white/10">
        <!-- Background Basketball Arena Photo (visible & high-contrast with lighter gradient) -->
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <img src="{{ asset('images/hero-arena.jpg') }}"
                 alt=""
                 class="h-full w-full object-cover object-center lg:object-right opacity-65 sm:opacity-75">
            <div class="absolute inset-0 bg-gradient-to-b from-[#050811]/90 via-[#070e22]/70 to-[#050811] lg:bg-gradient-to-r lg:from-[#050811]/95 lg:via-[#070e22]/65 lg:to-[#050811]/25"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#050811] via-transparent to-[#050811]/45"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 lg:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-8 items-center">
                <!-- Left Column: Plain/Taglish Copy & Single Consistent CTAs -->
                <div class="lg:col-span-6 space-y-5">
                    <div class="inline-flex items-center gap-2 rounded-full border border-orange-500/40 bg-[#0c1733]/90 px-3.5 py-1.5 text-xs sm:text-sm font-semibold text-orange-300 shadow-lg">
                        <span class="inline-block h-2 w-2 rounded-full bg-orange-500 animate-pulse"></span>
                        Para sa Inter-Purok, SK &amp; Municipal Leagues
                    </div>

                    <h1 class="font-display text-4xl sm:text-6xl lg:text-[4.25rem] font-extrabold uppercase italic tracking-tight leading-[0.95] text-white">
                        <span class="block">Run Your</span>
                        <span class="block bg-gradient-to-r from-amber-300 via-orange-400 to-orange-600 bg-clip-text text-transparent drop-shadow-[0_4px_24px_rgba(249,115,22,0.35)]">
                            Barangay League
                        </span>
                        <span class="block">Like the Pros.</span>
                    </h1>

                    <p class="max-w-xl text-base sm:text-lg text-[#C9D1E0] leading-relaxed">
                        Run your barangay league: schedules, team rosters, at live scores — all in one link. Mas madali i-share sa barangay Facebook page.
                    </p>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-1">
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-3.5 text-base font-bold text-white hoops-cta-glow transition-all duration-200 hover:-translate-y-0.5">
                            <span>Start Free</span>
                            <span aria-hidden="true">&rsaquo;</span>
                        </a>

                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-[#0c1733]/90 px-6 py-3.5 text-base font-bold text-white hover:border-orange-500/50 hover:bg-[#132247] transition">
                                <span>Go to Dashboard</span>
                                <span aria-hidden="true" class="text-orange-400">&rsaquo;</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-[#0c1733]/90 px-6 py-3.5 text-base font-bold text-white hover:border-orange-500/50 hover:bg-[#132247] transition">
                                <span>Sign In</span>
                                <span aria-hidden="true" class="text-orange-400">&rsaquo;</span>
                            </a>
                        @endauth
                    </div>

                    <p class="text-sm text-[#C9D1E0]">
                        Free trial: 1 tournament, up to 6 teams &bull; No credit card needed.
                    </p>
                </div>

                <!-- Right Column (and below text on mobile): Product Mockup -->
                <div class="lg:col-span-6 pb-2 sm:pb-6 lg:pb-8">
                    <x-hero-mockup />
                </div>
            </div>
        </div>
    </section>

    <!-- TRUST BAR: LGU / SK PARTNERS -->
    <section class="bg-[#070c1a] border-b border-white/10 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row items-center justify-between gap-5">
            <p class="text-sm font-semibold uppercase tracking-wider text-[#C9D1E0] text-center lg:text-left">
                Built with &amp; for Philippine Local Basketball Committees
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-5 w-full lg:w-auto">
                <div class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0c1733]/70 px-3.5 py-2.5 text-sm font-semibold text-white">
                    <svg class="h-5 w-5 text-orange-400 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15 8L22 9L17 14L18 21L12 18L6 21L7 14L2 9L9 8L12 2Z"/></svg>
                    <span>SK Federation</span>
                </div>
                <div class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0c1733]/70 px-3.5 py-2.5 text-sm font-semibold text-white">
                    <svg class="h-5 w-5 text-orange-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 4v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V7l8-4z"/></svg>
                    <span>Liga ng Barangay</span>
                </div>
                <div class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0c1733]/70 px-3.5 py-2.5 text-sm font-semibold text-white">
                    <svg class="h-5 w-5 text-orange-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3v18"/></svg>
                    <span>LGU Sports</span>
                </div>
                <div class="flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0c1733]/70 px-3.5 py-2.5 text-sm font-semibold text-white">
                    <svg class="h-5 w-5 text-orange-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M8 21h8M12 19v2"/></svg>
                    <span>Table Officials</span>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS (3 STEPS) -->
    <section id="how-it-works" class="bg-[#050811] border-b border-white/10 py-14 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400">Madaling Simulan</div>
                    <h2 class="mt-1 font-display text-3xl sm:text-4xl font-extrabold uppercase tracking-tight text-white">
                        How It Works in 3 Steps
                    </h2>
                </div>
                <a href="{{ route('register') }}" class="text-sm font-semibold text-orange-400 hover:text-orange-300">
                    Start Free &rarr;
                </a>
            </div>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="arena-glass rounded-2xl border border-white/10 p-6 space-y-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500/20 border border-orange-500/40 font-display text-xl font-extrabold text-orange-300">
                        1
                    </span>
                    <h3 class="font-display text-2xl font-bold text-white">Register Your Barangay</h3>
                    <p class="text-sm text-[#C9D1E0] leading-relaxed">
                        Sign up with your barangay name and get your own league page link in less than 2 minutes.
                    </p>
                </div>

                <div class="arena-glass rounded-2xl border border-white/10 p-6 space-y-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500/20 border border-orange-500/40 font-display text-xl font-extrabold text-orange-300">
                        2
                    </span>
                    <h3 class="font-display text-2xl font-bold text-white">Add Teams &amp; Players</h3>
                    <p class="text-sm text-[#C9D1E0] leading-relaxed">
                        Enter purok teams, coaches, and player jerseys. Duplicate numbers per team are blocked automatically.
                    </p>
                </div>

                <div class="arena-glass rounded-2xl border border-white/10 p-6 space-y-2.5">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500/20 border border-orange-500/40 font-display text-xl font-extrabold text-orange-300">
                        3
                    </span>
                    <h3 class="font-display text-2xl font-bold text-white">Share the Public Link</h3>
                    <p class="text-sm text-[#C9D1E0] leading-relaxed">
                        Post your link sa Barangay Facebook page so players, coaches, and fans see schedules and rosters anytime.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- LIVE BARANGAY LEAGUE PORTALS -->
    <section id="leagues" class="bg-[#070c1a] border-b border-white/10 py-14 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400">Public League Directory</div>
                    <h2 class="mt-1 font-display text-3xl sm:text-4xl font-extrabold uppercase tracking-tight text-white">
                        Live Barangay League Portals
                    </h2>
                    <p class="mt-1 text-base text-[#C9D1E0]">
                        Tingnan ang mga live barangay tournaments, teams, at rosters.
                    </p>
                </div>
                <a href="{{ route('register') }}" class="text-sm font-semibold text-orange-400 hover:text-orange-300 shrink-0">
                    Add your barangay &rarr;
                </a>
            </div>

            <div class="mt-7 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($tenants as $tenant)
                    <a href="{{ route('portal.show', $tenant) }}"
                       class="group arena-glass relative overflow-hidden rounded-2xl border border-white/10 p-5 flex items-start gap-4 transition-all duration-300 hover:-translate-y-1 hover:border-orange-500/60 hover:shadow-[0_14px_35px_rgba(249,115,22,0.18)]">
                        <div aria-hidden="true" class="pointer-events-none absolute -right-10 -bottom-10 h-28 w-28 rounded-full bg-orange-500/10 blur-2xl group-hover:bg-orange-500/20 transition-colors"></div>

                        <x-league-badge :tenant="$tenant" size="md" />

                        <div class="min-w-0 flex-1 space-y-1.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-display text-2xl font-bold tracking-tight text-white group-hover:text-orange-300 transition-colors break-words">
                                    {{ $tenant->name }}
                                </h3>
                                <span class="shrink-0 rounded-full border border-orange-500/35 bg-orange-500/15 px-2.5 py-0.5 text-xs font-bold text-orange-200">
                                    {{ $tenant->tournaments_count }} {{ Str::plural('League', $tenant->tournaments_count) }}
                                </span>
                            </div>

                            <p class="flex items-center gap-1.5 text-sm text-[#C9D1E0] break-words">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-orange-400 shrink-0"></span>
                                <span>{{ collect([$tenant->municipality, $tenant->province])->filter()->implode(', ') ?: 'Philippines' }}</span>
                            </p>

                            <div class="pt-1 inline-flex items-center gap-1.5 text-sm font-semibold text-orange-400 group-hover:text-orange-300">
                                <span>/l/{{ $tenant->slug }}</span>
                                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full arena-glass rounded-2xl border border-white/10 p-8 text-center space-y-3">
                        <p class="text-base font-semibold text-white">Be the first to launch your barangay league!</p>
                        <p class="text-sm text-[#C9D1E0]">Register your barangay today to get your public portal link.</p>
                        <a href="{{ route('register') }}" class="inline-flex rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-2.5 text-sm font-bold text-white">
                            Start Free
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- FEATURED TOURNAMENTS (Drafts are hidden) -->
    <section id="tournaments" class="bg-[#050811] border-b border-white/10 py-14 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400">Open &amp; Ongoing</div>
                    <h2 class="mt-1 font-display text-3xl sm:text-4xl font-extrabold uppercase tracking-tight text-white">
                        Featured Tournaments
                    </h2>
                </div>
                <a href="{{ route('register') }}" class="text-sm font-semibold text-orange-400 hover:text-orange-300 shrink-0">
                    Launch yours &rarr;
                </a>
            </div>

            <div class="mt-7 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($featuredTournaments as $tournament)
                    <a href="{{ route('portal.show', $tournament->tenant) }}"
                       class="group arena-glass rounded-2xl border border-white/10 p-5 hover:border-orange-500/50 transition-all duration-300 hover:-translate-y-0.5 block">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="rounded-full bg-[#132247] border border-blue-400/25 px-3 py-0.5 text-xs font-bold uppercase tracking-wider text-blue-200">
                                {{ $tournament->category->label() }}
                            </span>
                            <span class="rounded-full bg-orange-500/15 border border-orange-500/35 px-3 py-0.5 text-xs font-semibold text-orange-200">
                                {{ $tournament->status->label() }}
                            </span>
                        </div>

                        <h3 class="mt-3 font-display text-2xl font-bold tracking-tight text-white group-hover:text-orange-300 transition-colors">
                            {{ $tournament->name }}
                        </h3>
                        <p class="mt-1 text-sm text-[#C9D1E0]">
                            Hosted by <span class="text-white font-semibold">{{ $tournament->tenant->name }}</span>
                        </p>

                        <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between text-sm text-[#C9D1E0]">
                            <span>{{ $tournament->format_type->label() }}</span>
                            <span class="font-semibold text-white">{{ $tournament->teams_count }} Teams</span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full arena-glass rounded-2xl border border-white/10 p-8 text-center space-y-3">
                        <p class="text-base font-semibold text-white">Be the first to launch a live tournament!</p>
                        <p class="text-sm text-[#C9D1E0]">Once a barangay opens registration or tips off, it shows up right here.</p>
                        <a href="{{ route('register') }}" class="inline-flex rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-2.5 text-sm font-bold text-white">
                            Start Free
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- COMMUNITY PHOTO + TESTIMONIAL (TRUST SECTION) -->
    <section class="bg-[#070c1a] border-b border-white/10 py-14 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-6">
                    <div class="relative rounded-2xl overflow-hidden border border-white/15 shadow-2xl">
                        <img src="{{ asset('images/barangay-court.jpg') }}"
                             alt="Philippine barangay covered court basketball game at night"
                             class="w-full h-64 sm:h-80 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#050811]/85 via-transparent to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4 text-sm font-semibold text-white">
                            Inter-Purok Night Game &bull; Covered Court League Action
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6 space-y-5">
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400">From the Committee Table</div>
                    <blockquote class="font-display text-2xl sm:text-3xl font-bold leading-snug text-white">
                        &ldquo;Dati puro papel at Messenger ang schedule at listahan ng players namin. Ngayon isang link na lang ang pino-post namin sa Facebook page ng Barangay.&rdquo;
                    </blockquote>
                    <div class="flex items-center gap-3 pt-1">
                        <img src="{{ asset('images/logos/brgy-san-roque.svg') }}" alt="Barangay San Roque" class="h-12 w-12 object-contain">
                        <div>
                            <div class="text-base font-bold text-white">Comm. Marco Reyes</div>
                            <div class="text-sm text-[#C9D1E0]">Barangay San Roque &bull; SK Summer League</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES (Technical details moved cleanly into feature cards) -->
    <section id="about" class="bg-[#050811] border-b border-white/10 py-14 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400">Platform Features</div>
                    <h2 class="mt-1 font-display text-3xl sm:text-4xl font-extrabold uppercase tracking-tight text-white">
                        Everything Your Committee Needs
                    </h2>
                    <p class="mt-2 text-base text-[#C9D1E0]">
                        Simple on the surface, backed by tournament-grade controls underneath.
                    </p>
                </div>
                <a href="{{ route('register') }}" class="text-sm font-semibold text-orange-400 hover:text-orange-300 shrink-0">
                    Start Free &rarr;
                </a>
            </div>

            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="arena-glass rounded-2xl border border-white/10 p-6">
                    <div class="text-xs font-bold uppercase tracking-wider text-orange-400">01 &bull; Own Portal</div>
                    <h3 class="mt-2 font-display text-2xl font-bold text-white">Isolated League Space</h3>
                    <p class="mt-2 text-sm text-[#C9D1E0] leading-relaxed">
                        Multi-tenant isolation keeps every barangay&apos;s teams, scorers, and public portal link completely separate.
                    </p>
                </div>
                <div class="arena-glass rounded-2xl border border-white/10 p-6">
                    <div class="text-xs font-bold uppercase tracking-wider text-orange-400">02 &bull; Clean Rosters</div>
                    <h3 class="mt-2 font-display text-2xl font-bold text-white">Jersey &amp; Lock Rules</h3>
                    <p class="mt-2 text-sm text-[#C9D1E0] leading-relaxed">
                        Enforces jersey number uniqueness per team, tags homegrown vs. import players, and locks rosters after elimination game 2.
                    </p>
                </div>
                <div class="arena-glass rounded-2xl border border-white/10 p-6">
                    <div class="text-xs font-bold uppercase tracking-wider text-orange-400">03 &bull; Divisions</div>
                    <h3 class="mt-2 font-display text-2xl font-bold text-white">FIBA-Ready Formats</h3>
                    <p class="mt-2 text-sm text-[#C9D1E0] leading-relaxed">
                        Built for Midget, Junior, Senior, Open, and 40-Up brackets with Round Robin and Group + Knockout formats.
                    </p>
                </div>
                <div class="arena-glass rounded-2xl border border-white/10 p-6">
                    <div class="text-xs font-bold uppercase tracking-wider text-orange-400">04 &bull; Courtside</div>
                    <h3 class="mt-2 font-display text-2xl font-bold text-white">Event-Sourced Stats</h3>
                    <p class="mt-2 text-sm text-[#C9D1E0] leading-relaxed">
                        Play-by-play event sourcing supports clean play undos, live box scores, and automated BPG &amp; MVP leaderboards.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT / ONBOARDING CTA -->
    <section id="contact" class="bg-gradient-to-b from-[#070c1a] to-[#050811] py-14 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="arena-glass rounded-3xl border border-orange-500/35 p-6 sm:p-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 shadow-[0_20px_50px_rgba(249,115,22,0.12)]">
                <div class="max-w-2xl space-y-2">
                    <div class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400">Ready for Tip-Off?</div>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold uppercase tracking-tight text-white">
                        Launch Your Barangay League Today
                    </h2>
                    <p class="text-base text-[#C9D1E0]">
                        Start free with 1 tournament and up to 6 teams, or email us for Municipality-wide multi-division leagues.
                    </p>
                    <p class="text-sm text-[#C9D1E0]">
                        LGU &amp; SK Support: <span class="text-orange-300 font-semibold">hello@barangayhoops.ph</span>
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-3.5 text-base font-bold text-white hoops-cta-glow transition">
                        Start Free &rsaquo;
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-[#0c1733] px-6 py-3.5 text-base font-bold text-white hover:border-orange-500/50 transition">
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts::guest>
