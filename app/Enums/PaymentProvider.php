<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case PayMongo = 'paymongo';
    case Stripe = 'stripe';
    case Sandbox = 'sandbox';

    public function label(): string
    {
        return match ($this) {
            self::PayMongo => 'PayMongo (GCash / Maya / Bank)',
            self::Stripe => 'Stripe (Card)',
            self::Sandbox => 'Demo / Instant Activation',
        };
    }
}
