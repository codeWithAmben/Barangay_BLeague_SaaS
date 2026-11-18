<?php

namespace App\Models;

use App\Enums\PlayerPosition;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'team_id',
    'first_name',
    'last_name',
    'jersey_number',
    'position',
    'birthdate',
    'is_import',
    'is_suspended',
    'photo_path',
])]
class Player extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => PlayerPosition::class,
            'birthdate' => 'date',
            'is_import' => 'boolean',
            'is_suspended' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim("{$this->first_name} {$this->last_name}"),
        );
    }

    public function age(): ?int
    {
        return $this->birthdate?->age;
    }
}
