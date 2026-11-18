<?php

namespace App\Models;

use App\Enums\TournamentCategory;
use App\Enums\TournamentFormat;
use App\Enums\TournamentStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'tenant_id',
    'name',
    'category',
    'format_type',
    'status',
    'roster_locked',
    'roster_lock_after_game',
    'start_date',
    'end_date',
])]
class Tournament extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<TournamentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => TournamentCategory::class,
            'format_type' => TournamentFormat::class,
            'status' => TournamentStatus::class,
            'roster_locked' => 'boolean',
            'roster_lock_after_game' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * @return HasMany<Team, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * @return HasManyThrough<Player, Team, $this>
     */
    public function players(): HasManyThrough
    {
        return $this->hasManyThrough(Player::class, Team::class);
    }

    /**
     * @return HasMany<Game, $this>
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
