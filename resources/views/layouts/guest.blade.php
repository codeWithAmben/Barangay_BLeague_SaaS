<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Barangay Hoops — Barangay Basketball League SaaS' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-[#050811] text-base text-slate-100 antialiased flex flex-col selection:bg-orange-500 selection:text-white">
    <header class="sticky top-0 z-50 border-b border-white/10 bg-[#050811]/90 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="shrink-0">
                <x-brand-logo size="sm" />
            </a>

            <nav aria-label="Primary" class="hidden lg:flex items-center gap-7 text-sm font-medium text-[#C9D1E0]">
                <a href="{{ route('home') }}#home" class="hover:text-orange-400 transition-colors">Home</a>
                <a href="{{ route('home') }}#how-it-works" class="hover:text-orange-400 transition-colors">How It Works</a>
                <a href="{{ route('home') }}#tournaments" class="hover:text-orange-400 transition-colors">Tournaments</a>
                <a href="{{ route('home') }}#leagues" class="hover:text-orange-400 transition-colors">Leagues</a>
                <a href="{{ route('home') }}#about" class="hover:text-orange-400 transition-colors">Features</a>
                <a href="{{ route('home') }}#contact" class="hover:text-orange-400 transition-colors">Contact</a>
            </nav>

            <div class="hidden lg:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-2.5 text-sm font-semibold text-white hoops-cta-glow transition">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="rounded-xl border border-white/15 bg-[#0c1733]/90 px-4 py-2.5 text-sm font-semibold text-white hover:border-orange-500/50 transition">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}"
                       class="rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-5 py-2.5 text-sm font-semibold text-white hoops-cta-glow transition">
                        Start Free
                    </a>
                @endauth
            </div>

            <button type="button"
                    id="mobile-nav-toggle"
                    aria-expanded="false"
                    aria-controls="mobile-nav-menu"
                    onclick="const m=document.getElementById('mobile-nav-menu'); m.classList.toggle('hidden'); this.setAttribute('aria-expanded', !m.classList.contains('hidden'));"
                    class="lg:hidden inline-flex items-center justify-center rounded-xl border border-white/15 bg-[#0c1733] p-2.5 text-white hover:border-orange-500/50">
                <span class="sr-only">Toggle navigation</span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="mobile-nav-menu" class="hidden lg:hidden border-t border-white/10 bg-[#070d1d] px-4 py-4 space-y-3">
            <div class="grid grid-cols-2 gap-2 text-sm font-medium text-[#C9D1E0]">
                <a href="{{ route('home') }}#home" class="rounded-lg px-3 py-2.5 hover:bg-white/5 hover:text-orange-400">Home</a>
                <a href="{{ route('home') }}#how-it-works" class="rounded-lg px-3 py-2.5 hover:bg-white/5 hover:text-orange-400">How It Works</a>
                <a href="{{ route('home') }}#tournaments" class="rounded-lg px-3 py-2.5 hover:bg-white/5 hover:text-orange-400">Tournaments</a>
                <a href="{{ route('home') }}#leagues" class="rounded-lg px-3 py-2.5 hover:bg-white/5 hover:text-orange-400">Leagues</a>
                <a href="{{ route('home') }}#about" class="rounded-lg px-3 py-2.5 hover:bg-white/5 hover:text-orange-400">Features</a>
                <a href="{{ route('home') }}#contact" class="rounded-lg px-3 py-2.5 hover:bg-white/5 hover:text-orange-400">Contact</a>
            </div>
            <div class="pt-3 border-t border-white/10 flex flex-col sm:flex-row gap-2.5">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3 text-sm font-semibold text-white">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center rounded-xl border border-white/15 bg-[#0c1733] px-4 py-3 text-sm font-semibold text-white">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="w-full text-center rounded-xl bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3 text-sm font-semibold text-white">
                        Start Free
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-white/10 bg-[#050811] pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 pb-10 border-b border-white/10">
                <div class="lg:col-span-2 space-y-3">
                    <x-brand-logo size="sm" />
                    <p class="max-w-md text-sm text-[#C9D1E0] leading-relaxed">
                        Built for Philippine Inter-Purok, SK Councils, and Municipal basketball leagues.
                        Run your schedules, rosters, and live scores in one shareable link.
                    </p>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-orange-400">Explore</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[#C9D1E0]">
                        <li><a href="{{ route('home') }}#how-it-works" class="hover:text-white">How It Works</a></li>
                        <li><a href="{{ route('home') }}#leagues" class="hover:text-white">Live Barangay Leagues</a></li>
                        <li><a href="{{ route('home') }}#tournaments" class="hover:text-white">Active Tournaments</a></li>
                        <li><a href="{{ route('home') }}#about" class="hover:text-white">Platform Features</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-orange-400">Organizers</h3>
                    <ul class="mt-3 space-y-2 text-sm text-[#C9D1E0]">
                        <li><a href="{{ route('register') }}" class="hover:text-white">Start Free Trial</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white">Sign In</a></li>
                        <li><span class="text-[#C9D1E0]">hello@barangayhoops.ph</span></li>
                        <li><span class="text-[#C9D1E0]">Metro Manila &amp; Nationwide PH</span></li>
                    </ul>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-[#C9D1E0]">
                <div>&copy; {{ date('Y') }} BarangayHoops. All rights reserved.</div>
                <div>Made for Philippine Barangay Covered Courts</div>
            </div>
        </div>
    </footer>
</body>
</html>
