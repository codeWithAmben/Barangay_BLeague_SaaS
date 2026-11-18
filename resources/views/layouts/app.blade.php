<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — Barangay Hoops SaaS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    @php
        $user = auth()->user();
        $tenant = $user?->tenant;
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar -->
        <aside class="w-full lg:w-64 bg-slate-900 border-b lg:border-b-0 lg:border-r border-slate-800 flex flex-col justify-between">
            <div class="p-5">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-orange-600 text-white font-bold text-lg shadow-lg shadow-orange-600/30">
                        BB
                    </span>
                    <div>
                        <div class="text-sm font-bold tracking-tight text-white">Barangay Hoops</div>
                        <div class="text-xs text-slate-400">League SaaS</div>
                    </div>
                </a>

                @if ($tenant)
                    <div class="mt-5 rounded-xl bg-slate-800/70 border border-slate-700/60 p-3">
                        <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Active Barangay</div>
                        <div class="mt-1 font-semibold text-sm text-white truncate">{{ $tenant->name }}</div>
                        <div class="mt-1.5 flex items-center justify-between gap-2">
                            <span class="inline-flex items-center rounded-full bg-orange-500/15 px-2.5 py-0.5 text-xs font-medium text-orange-300 ring-1 ring-inset ring-orange-500/30">
                                {{ $tenant->plan_type->label() }}
                            </span>
                            <a href="{{ route('portal.show', $tenant) }}" target="_blank" class="text-xs text-sky-400 hover:text-sky-300">
                                Public Portal ↗
                            </a>
                        </div>
                    </div>
                @endif

                <nav class="mt-6 space-y-1">
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-orange-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('tournaments.index') }}" wire:navigate
                       class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('tournaments.*') ? 'bg-orange-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Tournaments
                    </a>
                    <a href="{{ route('teams.index') }}" wire:navigate
                       class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('teams.*') ? 'bg-orange-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Teams &amp; Puroks
                    </a>
                    <a href="{{ route('players.index') }}" wire:navigate
                       class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('players.*') ? 'bg-orange-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Players &amp; Rosters
                    </a>
                </nav>
            </div>

            <div class="p-5 border-t border-slate-800 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-sm font-medium text-white truncate">{{ $user?->name }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ $user?->role->label() }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700 cursor-pointer">
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 min-w-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
