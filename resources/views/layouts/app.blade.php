<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — Barangay Hoops SaaS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen arena-obsidian-bg font-sans text-slate-100 antialiased overflow-x-hidden selection:bg-orange-500 selection:text-white">
    @php
        $user = auth()->user();
        $tenant = $user?->tenant;
        $userInitials = collect(explode(' ', (string) $user?->name))
            ->filter()
            ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('') ?: 'BH';
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="w-full lg:w-72 shrink-0 bg-[#080b13]/95 backdrop-blur-xl border-b lg:border-b-0 lg:border-r border-white/10 flex flex-col justify-between">
            <div class="p-5 sm:p-6">
                <a href="{{ route('dashboard') }}" wire:navigate class="inline-block">
                    <x-brand-logo size="sm" />
                </a>

                @if ($tenant)
                    <div class="mt-6 arena-card rounded-2xl p-4 relative overflow-hidden">
                        <div class="flex items-center justify-between gap-2">
                            <div class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                                Active Barangay
                            </div>
                            <span class="inline-flex items-center rounded-full bg-amber-500/15 border border-amber-400/35 px-2.5 py-0.5 text-[11px] font-semibold text-amber-300 shadow-[0_0_12px_rgba(245,158,11,0.18)]">
                                {{ $tenant->plan_type->label() }}
                            </span>
                        </div>

                        <div class="mt-2.5 flex items-center gap-3">
                            <x-league-badge :tenant="$tenant" size="sm" />
                            <div class="min-w-0">
                                <div class="font-display text-lg font-extrabold tracking-tight text-white truncate">
                                    {{ $tenant->name }}
                                </div>
                                <a href="{{ route('portal.show', $tenant) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs font-medium text-orange-400 hover:text-orange-300 transition">
                                    <span>Public Portal</span>
                                    <span aria-hidden="true">↗</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <nav class="mt-6 space-y-1.5">
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    @if ($user?->role !== \App\Enums\UserRole::Scorer)
                        <a href="{{ route('tournaments.index') }}" wire:navigate
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('tournaments.*') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('tournaments.*') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                            </svg>
                            <span>Tournaments</span>
                        </a>
                        <a href="{{ route('teams.index') }}" wire:navigate
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('teams.*') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('teams.*') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>Teams &amp; Puroks</span>
                        </a>
                        <a href="{{ route('players.index') }}" wire:navigate
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('players.*') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('players.*') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <span>Players &amp; Rosters</span>
                        </a>
                        <a href="{{ route('schedule.index') }}" wire:navigate
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('schedule.*') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('schedule.*') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                            <span>Schedule &amp; Standings</span>
                        </a>
                        <a href="{{ route('awards.index') }}" wire:navigate
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('awards.*') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('awards.*') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15 8.5 22 9.3 17 14 18.2 21 12 17.7 5.8 21 7 14 2 9.3 9 8.5 12 2"/>
                            </svg>
                            <span>Awards &amp; Leaders</span>
                        </a>
                        <a href="{{ route('billing.index') }}" wire:navigate
                           class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('billing.*') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                            <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('billing.*') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>
                            </svg>
                            <span>Billing &amp; Plan</span>
                        </a>
                    @endif
                    <a href="{{ route('scorer.index') }}" wire:navigate
                       class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition {{ request()->routeIs('scorer.*') ? 'nav-pill-active' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-4 w-4 shrink-0 {{ request()->routeIs('scorer.*') ? 'text-amber-300' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
                        </svg>
                        <span>Live Scorer</span>
                    </a>
                </nav>
            </div>

            <div class="p-5 border-t border-white/10 bg-black/25 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-orange-500/30 to-amber-500/15 border border-orange-500/40 text-xs font-bold text-orange-200">
                        {{ $userInitials }}
                    </span>
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-white truncate">{{ $user?->name }}</div>
                        <div class="text-xs text-slate-400 truncate">{{ $user?->role->label() }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-200 hover:border-orange-500/40 hover:bg-white/10 transition cursor-pointer">
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 min-w-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200 shadow-[0_0_24px_rgba(16,185,129,0.12)]">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
