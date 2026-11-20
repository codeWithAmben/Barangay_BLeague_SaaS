<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PlanType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'user_id',
    'reference',
    'provider',
    'plan_type',
    'amount_centavos',
    'currency',
    'status',
    'provider_session_id',
    'checkout_url',
    'paid_at',
])]
class PaymentTransaction extends Model
{
    use BelongsToTenant;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider' => PaymentProvider::class,
            'plan_type' => PlanType::class,
            'status' => PaymentStatus::class,
            'amount_centavos' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedAmount(): string
    {
        return '₱'.number_format($this->amount_centavos / 100, 2);
    }
}
