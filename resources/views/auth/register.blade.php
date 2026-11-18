<x-layouts::guest title="Register Barangay League">
    <div class="max-w-lg mx-auto px-4 py-12">
        <div class="rounded-2xl border border-slate-800 bg-slate-900 p-8 shadow-xl">
            <h1 class="text-2xl font-bold text-white">Launch Your Barangay League</h1>
            <p class="mt-1 text-sm text-slate-400">
                Start with a free Trial workspace (1 tournament, up to 6 teams) and get a public league portal instantly.
            </p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="barangay_name" class="block text-xs font-medium text-slate-300 mb-1">Barangay / League Name</label>
                    <input id="barangay_name" name="barangay_name" type="text" value="{{ old('barangay_name') }}" required
                           placeholder="e.g., Barangay San Roque"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('barangay_name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="municipality" class="block text-xs font-medium text-slate-300 mb-1">City / Municipality</label>
                        <input id="municipality" name="municipality" type="text" value="{{ old('municipality') }}"
                               placeholder="e.g., Marikina City"
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @error('municipality') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="province" class="block text-xs font-medium text-slate-300 mb-1">Province / Region</label>
                        <input id="province" name="province" type="text" value="{{ old('province') }}"
                               placeholder="e.g., Metro Manila"
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @error('province') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="slug" class="block text-xs font-medium text-slate-300 mb-1">Portal URL Slug (optional)</label>
                    <div class="flex items-center rounded-xl border border-slate-700 bg-slate-950 overflow-hidden">
                        <span class="px-3 text-xs text-slate-500 border-r border-slate-800">/l/</span>
                        <input id="slug" name="slug" type="text" value="{{ old('slug') }}" placeholder="brgy-san-roque"
                               class="w-full bg-transparent px-3 py-2.5 text-sm text-white focus:outline-none">
                    </div>
                    @error('slug') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <hr class="border-slate-800 my-2">

                <div>
                    <label for="name" class="block text-xs font-medium text-slate-300 mb-1">Commissioner Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                           placeholder="e.g., Marco Reyes"
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('name') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    @error('email') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-300 mb-1">Password</label>
                        <input id="password" name="password" type="password" required
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                        @error('password') <p class="mt-1 text-xs text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-slate-300 mb-1">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:outline-none">
                    </div>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-500 cursor-pointer">
                    Create Barangay Workspace
                </button>
            </form>
        </div>
    </div>
</x-layouts::guest>
