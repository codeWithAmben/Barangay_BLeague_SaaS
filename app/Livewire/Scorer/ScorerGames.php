<?php

namespace App\Livewire\Scorer;

use App\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Courtside Scorer Matches')]
class ScorerGames extends Component
{
    public function render(): View
    {
        $games = Game::query()
            ->with(['tournament', 'homeTeam', 'awayTeam'])
            ->orderByRaw("CASE status WHEN 'live' THEN 1 WHEN 'scheduled' THEN 2 ELSE 3 END")
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->get();

        return view('livewire.scorer.scorer-games', [
            'liveGames' => $games->where('status', GameStatus::Live),
            'scheduledGames' => $games->where('status', GameStatus::Scheduled),
            'finishedGames' => $games->where('status', GameStatus::Finished),
        ]);
    }
}
