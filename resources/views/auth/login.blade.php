<x-layouts::guest title="Sign In">
    <div class="max-w-md mx-auto px-4 py-16">
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-8 shadow-xl">
            <h1 class="text-2xl font-bold text-white">Sign in to Barangay Hoops</h1>
            <p class="mt-1 text-sm text-slate-400">Access your barangay's tournaments, teams, and rosters.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('email') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('password') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <label class="inline-flex items-center gap-2 text-xs text-slate-400">
                    <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-950 text-orange-600">
                    Keep me signed in
                </label>

                <button type="submit"
                        class="w-full rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
                    Sign In
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-400">
                New barangay organizer?
                <a href="{{ route('register') }}" class="text-orange-400 hover:text-orange-300 font-medium">Register your Barangay League</a>
            </p>
        </div>
    </div>
</x-layouts::guest>
