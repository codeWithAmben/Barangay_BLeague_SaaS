<?php

namespace App\Models;

use App\Enums\PlanType;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'municipality',
    'province',
    'logo_path',
    'plan_type',
    'subscription_ends_at',
])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan_type' => PlanType::class,
            'subscription_ends_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Tournament, $this>
     */
    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    /**
     * @return HasMany<Team, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * @return HasMany<Player, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function canCreateTournament(): bool
    {
        $max = $this->plan_type->maxTournaments();

        if ($max === null) {
            return true;
        }

        return $this->tournaments()->count() < $max;
    }

    public function canAddTeam(Tournament $tournament): bool
    {
        $max = $this->plan_type->maxTeamsPerTournament();

        if ($max === null) {
            return true;
        }

        return $tournament->teams()->count() < $max;
    }
}
