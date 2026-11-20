<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingWebhookController extends Controller
{
    public function paymongo(Request $request, BillingService $billingService): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = $request->header('Paymongo-Signature');

        if (! $billingService->verifyPayMongoSignature($rawBody, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $eventType = (string) $request->input('data.attributes.type');

        if ($eventType === 'checkout_session.payment.paid') {
            $sessionId = (string) $request->input('data.attributes.data.id');
            $reference = (string) $request->input('data.attributes.data.attributes.reference_number');

            $transaction = PaymentTransaction::withoutGlobalScopes()
                ->where(function ($query) use ($sessionId, $reference): void {
                    if ($sessionId !== '') {
                        $query->where('provider_session_id', $sessionId);
                    }
                    if ($reference !== '') {
                        $query->orWhere('reference', $reference);
                    }
                })
                ->first();

            if ($transaction !== null) {
                $billingService->markTransactionPaid($transaction);
            }
        }

        return response()->json(['received' => true]);
    }

    public function stripe(Request $request, BillingService $billingService): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! $billingService->verifyStripeSignature($rawBody, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if ($request->input('type') === 'checkout.session.completed') {
            $sessionId = (string) $request->input('data.object.id');
            $reference = (string) $request->input('data.object.client_reference_id');

            $transaction = PaymentTransaction::withoutGlobalScopes()
                ->where(function ($query) use ($sessionId, $reference): void {
                    if ($sessionId !== '') {
                        $query->where('provider_session_id', $sessionId);
                    }
                    if ($reference !== '') {
                        $query->orWhere('reference', $reference);
                    }
                })
                ->first();

            if ($transaction !== null) {
                $billingService->markTransactionPaid($transaction);
            }
        }

        return response()->json(['received' => true]);
    }
}
