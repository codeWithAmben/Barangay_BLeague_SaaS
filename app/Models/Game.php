<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Models\Concerns\BelongsToTenant;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'tournament_id',
    'home_team_id',
    'away_team_id',
    'best_player_id',
    'scheduled_at',
    'venue',
    'round_number',
    'status',
    'home_score',
    'away_score',
    'current_quarter',
])]
class Game extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'round_number' => 'integer',
            'status' => GameStatus::class,
            'home_score' => 'integer',
            'away_score' => 'integer',
            'current_quarter' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Tournament, $this>
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function bestPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'best_player_id');
    }

    /**
     * @return HasMany<GameRoster, $this>
     */
    public function rosters(): HasMany
    {
        return $this->hasMany(GameRoster::class);
    }

    /**
     * @return HasMany<GameEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(GameEvent::class);
    }
}
