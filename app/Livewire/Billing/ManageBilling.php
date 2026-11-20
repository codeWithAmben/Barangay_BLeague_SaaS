<?php

namespace App\Livewire\Billing;

use App\Enums\PaymentProvider;
use App\Enums\PlanType;
use App\Enums\UserRole;
use App\Models\PaymentTransaction;
use App\Services\BillingService;
use App\Services\TenantContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts::app')]
#[Title('Billing & Subscription')]
class ManageBilling extends Component
{
    public string $provider = 'paymongo';

    public ?string $errorBanner = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user === null || ! in_array($user->role, [UserRole::Commissioner, UserRole::SuperAdmin], true)) {
            abort(403);
        }
    }

    public function subscribe(string $planValue, BillingService $billingService, TenantContext $tenantContext): mixed
    {
        $this->errorBanner = null;

        $this->validate([
            'provider' => ['required', Rule::enum(PaymentProvider::class)],
        ]);

        $plan = PlanType::tryFrom($planValue);
        $provider = PaymentProvider::tryFrom($this->provider);
        $tenant = $tenantContext->get();
        $user = auth()->user();

        if ($plan === null || $provider === null || $tenant === null || $user === null) {
            $this->errorBanner = 'Invalid plan or workspace context.';

            return null;
        }

        try {
            $transaction = $billingService->createCheckout(
                tenant: $tenant,
                user: $user,
                plan: $plan,
                provider: $provider,
                successUrl: route('billing.index'),
                cancelUrl: route('billing.index'),
            );

            if ($transaction->checkout_url !== null) {
                return redirect()->away($transaction->checkout_url);
            }

            session()->flash('status', "Subscription upgraded to {$plan->label()}! Reference: {$transaction->reference}");
        } catch (RuntimeException $exception) {
            $this->errorBanner = $exception->getMessage();
        }

        return null;
    }

    public function render(TenantContext $tenantContext): View
    {
        return view('livewire.billing.manage-billing', [
            'tenant' => $tenantContext->get(),
            'plans' => config('billing.plans'),
            'demoMode' => (bool) config('billing.demo_mode'),
            'transactions' => PaymentTransaction::query()->latest()->limit(15)->get(),
        ]);
    }
}
