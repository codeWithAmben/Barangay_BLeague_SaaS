<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400 shadow-[0_0_10px_rgba(249,115,22,0.6)]"></span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold italic uppercase tracking-tight text-white">Billing &amp; Subscription</h1>
            </div>
            <p class="mt-1 text-sm text-slate-300">
                Upgrade your Barangay League plan via PayMongo (GCash, Maya, Bank) or Stripe.
            </p>
        </div>

        @if ($tenant)
            <div class="rounded-2xl border border-amber-400/35 bg-amber-500/10 px-4 py-2 text-xs text-amber-200">
                Current Plan: <strong class="text-white">{{ $tenant->plan_type->label() }}</strong>
                @if ($tenant->subscription_ends_at)
                    &bull; Active until {{ $tenant->subscription_ends_at->format('M j, Y') }}
                @endif
            </div>
        @endif
    </div>

    @if ($errorBanner)
        <div class="rounded-2xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            {{ $errorBanner }}
        </div>
    @endif

    <!-- Payment Method Selector -->
    <div class="arena-card rounded-2xl p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-4">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Payment Gateway:</span>
            <label class="inline-flex items-center gap-2 text-sm text-white cursor-pointer">
                <input wire:model="provider" type="radio" value="paymongo" class="text-orange-600">
                <span>PayMongo (GCash / Maya / Bank)</span>
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-white cursor-pointer">
                <input wire:model="provider" type="radio" value="stripe" class="text-orange-600">
                <span>Stripe (Card)</span>
            </label>
        </div>
        @if ($demoMode)
            <span class="rounded-full border border-emerald-500/35 bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-300">
                Demo Mode Active (Instant Activation)
            </span>
        @endif
    </div>

    <!-- Plan Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach ($plans as $planKey => $plan)
            @php
                $isCurrent = $tenant?->plan_type->value === $planKey;
                $isPaid = $planKey !== 'trial';
            @endphp
            <div class="arena-card rounded-2xl p-6 flex flex-col justify-between gap-5 {{ $planKey === 'barangay_pro' ? 'border-orange-500/50' : '' }}">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="font-display text-2xl font-bold text-white">{{ $plan['name'] }}</h2>
                        @if ($isCurrent)
                            <span class="rounded-full bg-emerald-500/20 border border-emerald-500/40 px-2.5 py-0.5 text-[11px] font-bold text-emerald-300">Current</span>
                        @endif
                    </div>
                    <div class="font-display text-4xl font-extrabold italic text-orange-400">
                        &#8369;{{ number_format($plan['price_php']) }}
                        <span class="text-xs font-sans not-italic font-normal text-slate-400">/ month</span>
                    </div>
                    <p class="text-sm text-slate-300 leading-relaxed">{{ $plan['description'] }}</p>
                </div>

                @if ($isPaid)
                    <button wire:click="subscribe('{{ $planKey }}')" type="button"
                            class="btn-hoops-primary w-full rounded-xl px-4 py-2.5 text-sm font-bold text-white cursor-pointer">
                        {{ $isCurrent ? 'Extend ' . $plan['name'] : 'Upgrade to ' . $plan['name'] }}
                    </button>
                @else
                    <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-center text-xs font-semibold text-slate-300">
                        Included at Registration
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Transaction History -->
    <div class="arena-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/10 flex items-center gap-3">
            <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-amber-400"></span>
            <h2 class="font-display text-2xl font-bold uppercase text-white">Payment History</h2>
        </div>

        @if ($transactions->isEmpty())
            <div class="p-8 text-center text-sm text-slate-300">
                No payment transactions recorded yet.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10 text-sm">
                    <thead class="bg-black/40 text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-5 py-3 text-left">Reference</th>
                            <th class="px-5 py-3 text-left">Plan</th>
                            <th class="px-5 py-3 text-left">Provider</th>
                            <th class="px-5 py-3 text-left">Amount</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach ($transactions as $tx)
                            <tr>
                                <td class="px-5 py-3 font-mono text-xs text-white">{{ $tx->reference }}</td>
                                <td class="px-5 py-3 text-slate-200 font-medium">{{ $tx->plan_type->label() }}</td>
                                <td class="px-5 py-3 text-slate-300">{{ $tx->provider->label() }}</td>
                                <td class="px-5 py-3 font-bold text-orange-300">{{ $tx->formattedAmount() }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $tx->status === \App\Enums\PaymentStatus::Paid ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300' }}">
                                        {{ $tx->status->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-slate-400">{{ $tx->created_at->format('M j, Y g:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
