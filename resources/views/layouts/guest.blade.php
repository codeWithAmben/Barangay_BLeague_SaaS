<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Barangay Basketball League SaaS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased flex flex-col">
    <header class="border-b border-slate-800/80 bg-slate-900/80 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-orange-600 text-white font-bold shadow shadow-orange-600/30">
                    BB
                </span>
                <span class="font-bold tracking-tight text-white">Barangay Hoops</span>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-500">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-300 hover:text-white">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-500">
                        Register Barangay
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
        Barangay Basketball League SaaS &bull; Built for Philippine Inter-Purok, SK &amp; Municipal Leagues
    </footer>
</body>
</html>
