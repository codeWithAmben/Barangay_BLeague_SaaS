<?php

namespace App\Services;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Models\PaymentTransaction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class BillingService
{
    public function createCheckout(
        Tenant $tenant,
        User $user,
        PlanType $plan,
        PaymentProvider $provider,
        string $successUrl,
        string $cancelUrl,
    ): PaymentTransaction {
        $planConfig = config("billing.plans.{$plan->value}");

        if (! is_array($planConfig) || $plan === PlanType::Trial) {
            throw new RuntimeException('Select a paid subscription tier to check out.');
        }

        $amountCentavos = (int) $planConfig['price_centavos'];
        $reference = 'BH-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));

        $transaction = PaymentTransaction::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'reference' => $reference,
            'provider' => $provider,
            'plan_type' => $plan,
            'amount_centavos' => $amountCentavos,
            'currency' => (string) config('billing.currency', 'PHP'),
            'status' => PaymentStatus::Pending,
        ]);

        if ($provider === PaymentProvider::PayMongo && filled(config('billing.paymongo.secret_key'))) {
            return $this->initiatePayMongoCheckout($transaction, $planConfig['name'], $successUrl, $cancelUrl);
        }

        if ($provider === PaymentProvider::Stripe && filled(config('billing.stripe.secret_key'))) {
            return $this->initiateStripeCheckout($transaction, $planConfig['name'], $successUrl, $cancelUrl);
        }

        if (config('billing.demo_mode')) {
            return $this->markTransactionPaid($transaction);
        }

        throw new RuntimeException("{$provider->label()} API keys are not configured in .env.");
    }

    public function markTransactionPaid(PaymentTransaction $transaction): PaymentTransaction
    {
        return DB::transaction(function () use ($transaction): PaymentTransaction {
            if ($transaction->status !== PaymentStatus::Paid) {
                $transaction->update([
                    'status' => PaymentStatus::Paid,
                    'paid_at' => now(),
                ]);
            }

            $durationMonths = max(1, (int) config("billing.plans.{$transaction->plan_type->value}.duration_months", 1));
            $tenant = Tenant::query()->findOrFail($transaction->tenant_id);

            $baseDate = ($tenant->subscription_ends_at !== null && $tenant->subscription_ends_at->isFuture())
                ? $tenant->subscription_ends_at
                : now();

            $tenant->update([
                'plan_type' => $transaction->plan_type,
                'subscription_ends_at' => $baseDate->copy()->addMonths($durationMonths),
            ]);

            return $transaction->fresh();
        });
    }

    public function verifyPayMongoSignature(string $payload, ?string $header): bool
    {
        $secret = (string) config('billing.paymongo.webhook_secret');

        if ($secret === '' || blank($header)) {
            return false;
        }

        $parts = [];

        foreach (explode(',', $header) as $segment) {
            [$k, $v] = array_pad(explode('=', trim($segment), 2), 2, '');
            $parts[$k] = $v;
        }

        $timestamp = $parts['t'] ?? '';
        $signature = $parts['te'] ?? ($parts['li'] ?? '');

        if ($timestamp === '' || $signature === '') {
            return false;
        }

        $computed = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        return hash_equals($computed, $signature);
    }

    public function verifyStripeSignature(string $payload, ?string $header): bool
    {
        $secret = (string) config('billing.stripe.webhook_secret');

        if ($secret === '' || blank($header)) {
            return false;
        }

        $parts = [];

        foreach (explode(',', $header) as $segment) {
            [$k, $v] = array_pad(explode('=', trim($segment), 2), 2, '');
            $parts[$k] = $v;
        }

        $timestamp = $parts['t'] ?? '';
        $signature = $parts['v1'] ?? '';

        if ($timestamp === '' || $signature === '') {
            return false;
        }

        $computed = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        return hash_equals($computed, $signature);
    }

    private function initiatePayMongoCheckout(
        PaymentTransaction $transaction,
        string $planName,
        string $successUrl,
        string $cancelUrl,
    ): PaymentTransaction {
        $response = Http::withBasicAuth((string) config('billing.paymongo.secret_key'), '')
            ->post(rtrim((string) config('billing.paymongo.base_url'), '/').'/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'send_email_receipt' => true,
                        'show_description' => true,
                        'show_line_items' => true,
                        'description' => "BarangayHoops {$planName} Subscription ({$transaction->reference})",
                        'reference_number' => $transaction->reference,
                        'payment_method_types' => ['gcash', 'paymaya', 'grab_pay', 'card', 'dob'],
                        'line_items' => [[
                            'currency' => $transaction->currency,
                            'amount' => $transaction->amount_centavos,
                            'name' => "BarangayHoops — {$planName}",
                            'quantity' => 1,
                        ]],
                        'success_url' => $successUrl.'?reference='.$transaction->reference,
                        'cancel_url' => $cancelUrl,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            $transaction->update(['status' => PaymentStatus::Failed]);

            throw new RuntimeException('PayMongo checkout session could not be created.');
        }

        $data = $response->json('data');

        $transaction->update([
            'provider_session_id' => $data['id'] ?? null,
            'checkout_url' => $data['attributes']['checkout_url'] ?? null,
        ]);

        return $transaction->fresh();
    }

    private function initiateStripeCheckout(
        PaymentTransaction $transaction,
        string $planName,
        string $successUrl,
        string $cancelUrl,
    ): PaymentTransaction {
        $response = Http::asForm()
            ->withToken((string) config('billing.stripe.secret_key'))
            ->post(rtrim((string) config('billing.stripe.base_url'), '/').'/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => $successUrl.'?reference='.$transaction->reference,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => $transaction->reference,
                'line_items[0][price_data][currency]' => strtolower($transaction->currency),
                'line_items[0][price_data][product_data][name]' => "BarangayHoops — {$planName}",
                'line_items[0][price_data][unit_amount]' => $transaction->amount_centavos,
                'line_items[0][quantity]' => 1,
            ]);

        if (! $response->successful()) {
            $transaction->update(['status' => PaymentStatus::Failed]);

            throw new RuntimeException('Stripe checkout session could not be created.');
        }

        $data = $response->json();

        $transaction->update([
            'provider_session_id' => $data['id'] ?? null,
            'checkout_url' => $data['url'] ?? null,
        ]);

        return $transaction->fresh();
    }
}
