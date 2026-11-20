<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $summary
     */
    public function __construct(
        public Game $game,
        public array $summary = [],
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('game.'.$this->game->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'game.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'game_id' => $this->game->id,
            'home_score' => $this->game->home_score,
            'away_score' => $this->game->away_score,
            'current_quarter' => $this->game->current_quarter,
            'status' => $this->game->status->value,
            'best_player_id' => $this->game->best_player_id,
            'summary' => $this->summary,
        ];
    }
}
