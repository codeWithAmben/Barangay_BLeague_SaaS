<x-layouts::guest title="Barangay Hoops — Basketball League SaaS">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="max-w-3xl">
            <span class="inline-flex items-center rounded-full bg-orange-500/15 px-3 py-1 text-xs font-semibold text-orange-300 ring-1 ring-inset ring-orange-500/30">
                Philippine Inter-Purok, SK &amp; Municipal League Platform
            </span>
            <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold tracking-tight text-white">
                Run every Barangay Basketball League like the pros.
            </h1>
            <p class="mt-4 text-lg text-slate-300">
                Multi-tenant tournament management, purok team &amp; player rosters, jersey uniqueness validation,
                and public league portals built for Philippine covered courts.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <a href="{{ route('register') }}" class="rounded-xl bg-orange-600 px-6 py-3 text-sm font-semibold text-white hover:bg-orange-500 shadow-lg shadow-orange-600/25">
                    Start Free Barangay Trial
                </a>
                <a href="{{ route('login') }}" class="rounded-xl border border-slate-700 bg-slate-900 px-6 py-3 text-sm font-semibold text-slate-200 hover:bg-slate-800">
                    Commissioner Sign In
                </a>
            </div>
        </div>

        <div class="mt-16">
            <h2 class="text-lg font-semibold text-white">Live Barangay League Portals</h2>
            <p class="mt-1 text-sm text-slate-400">Browse public tournament pages hosted on the platform.</p>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($tenants as $tenant)
                    <a href="{{ route('portal.show', $tenant) }}"
                       class="rounded-2xl border border-slate-800 bg-slate-900/80 p-5 hover:border-orange-500/50 transition block">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-semibold text-white">{{ $tenant->name }}</h3>
                            <span class="rounded-full bg-slate-800 px-2.5 py-0.5 text-xs text-slate-300">
                                {{ $tenant->tournaments_count }} {{ Str::plural('league', $tenant->tournaments_count) }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ collect([$tenant->municipality, $tenant->province])->filter()->implode(', ') ?: 'Philippines' }}
                        </p>
                        <div class="mt-3 text-xs font-medium text-orange-400">/l/{{ $tenant->slug }} →</div>
                    </a>
                @empty
                    <div class="col-span-full rounded-2xl border border-slate-800 bg-slate-900/60 p-8 text-center text-sm text-slate-400">
                        No barangays registered yet. Be the first to launch your league!
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts::guest>
